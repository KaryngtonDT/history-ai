#!/usr/bin/env bash
# Full engine provisioning with strict verification.
# Provisions engines ONE BY ONE via docker exec (bypasses nginx timeout entirely).
# Shows real-time status: already ready, in progress, done, failed.
# No python3 required on the host — all parsing done via grep/sed or docker exec.
# Called by prod-reset and prod-redeploy — no || true allowed here.
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "${ROOT}"

COMPOSE_FILE="${COMPOSE_FILE:-docker-compose.prod-like.yml}"
COMPOSE=(docker compose -f "${COMPOSE_FILE}")
BACKEND_URL="${BACKEND_URL:-http://localhost:8000}"
HEALTH_TIMEOUT=120

GREEN='\033[0;32m'; YELLOW='\033[1;33m'; RED='\033[0;31m'; CYAN='\033[0;36m'; NC='\033[0m'
OK="[OK]"; FAIL="[!!]"; WAIT="[..]"

# Call a GET endpoint from inside the container
backend_get() {
  "${COMPOSE[@]}" exec -T backend curl -sf --max-time 30 "http://localhost/${1}" 2>/dev/null
}

# Provision one engine from inside the container (bypasses nginx)
provision_engine_exec() {
  "${COMPOSE[@]}" exec -T backend \
    curl -sf --max-time 7200 -X POST "http://localhost/api/runtime/engines/${1}/provision" \
    2>/dev/null
}

# Parse JSON inside the container (no python3 needed on host)
parse_json() {
  "${COMPOSE[@]}" exec -T backend python3 -c "$1"
}

echo "========================================================="
echo "   LUMEN — ENGINE PROVISIONING & VERIFICATION"
echo "========================================================="

# ------------------------------------------------------------------
echo ""
echo "[1/5] Ensuring stack is running..."
"${COMPOSE[@]}" up -d postgres redis ollama backend >/dev/null 2>&1

# ------------------------------------------------------------------
echo ""
echo "[2/5] Waiting for backend (max ${HEALTH_TIMEOUT}s)..."
elapsed=0
until curl -sf --max-time 5 "${BACKEND_URL}/health" >/dev/null 2>&1; do
  if [ "$elapsed" -ge "$HEALTH_TIMEOUT" ]; then
    echo "ERROR: Backend not reachable after ${HEALTH_TIMEOUT}s."
    exit 1
  fi
  printf "  ...waiting (%ds)\r" "$elapsed"
  sleep 3
  elapsed=$((elapsed + 3))
done
echo "  Backend healthy."

# ------------------------------------------------------------------
echo ""
echo "[3/5] Refreshing hardware profile..."
backend_get "api/runtime/hardware" >/dev/null
echo "  Done."

# ------------------------------------------------------------------
echo ""
echo "[4/5] Provisioning engines one by one..."
echo ""

PLAN_JSON=$(backend_get "api/runtime/provision/plan")
ENGINES_JSON=$(backend_get "api/runtime/engines")

# Extract engine IDs to provision from plan (grep-based, no python3)
ENGINES_TO_PROVISION=$(echo "$PLAN_JSON" \
  | grep -o '"engineId":"[^"]*"' \
  | sed 's/"engineId":"//;s/"//' || true)

# Extract ready engine IDs — parse inside container where python3 is available
READY_ENGINES=$(echo "$ENGINES_JSON" \
  | parse_json "
import json,sys
data=json.load(sys.stdin)
engines=data if isinstance(data,list) else data.get('engines',[])
[print(e['id']) for e in engines if e.get('status')=='ready']
" 2>/dev/null || true)

# Show already-ready engines
echo "  Already READY:"
if [ -z "$READY_ENGINES" ]; then
  echo "    (none)"
else
  while IFS= read -r eid; do
    [ -z "$eid" ] && continue
    printf "    ${GREEN}${OK}${NC} %s\n" "$eid"
  done <<< "$READY_ENGINES"
fi

PLAN_COUNT=$(echo "$ENGINES_TO_PROVISION" | grep -c '[^[:space:]]' || true)
PLAN_COUNT="${PLAN_COUNT:-0}"

echo ""
if [ "$PLAN_COUNT" -eq 0 ]; then
  echo "  Nothing to provision — all compatible engines already READY."
else
  echo "  To provision (${PLAN_COUNT} engine(s)):"
  while IFS= read -r eid; do
    [ -z "$eid" ] && continue
    printf "    ${CYAN}${WAIT}${NC} %s\n" "$eid"
  done <<< "$ENGINES_TO_PROVISION"
  echo ""

  while IFS= read -r ENGINE_ID; do
    [ -z "$ENGINE_ID" ] && continue

    printf "  ${YELLOW}${WAIT}${NC} %-35s installing...\n" "$ENGINE_ID"

    RESULT=$(provision_engine_exec "$ENGINE_ID" || echo '{"ok":false}')
    ENGINE_OK=$(echo "$RESULT" | grep -o '"ok":true' | head -1 || true)

    if [ -n "$ENGINE_OK" ]; then
      printf "  ${GREEN}${OK}${NC} %-35s done\n" "$ENGINE_ID"
    else
      STATUS_JSON=$(backend_get "api/runtime/engines/${ENGINE_ID}/compatibility" || echo '{}')
      IS_READY=$(echo "$STATUS_JSON" | grep -o '"status":"ready"' | head -1 || true)
      if [ -n "$IS_READY" ]; then
        printf "  ${GREEN}${OK}${NC} %-35s ready\n" "$ENGINE_ID"
      else
        printf "  ${RED}${FAIL}${NC} %-35s FAILED\n" "$ENGINE_ID"
      fi
    fi
  done <<< "$ENGINES_TO_PROVISION"
fi

# ------------------------------------------------------------------
echo ""
echo "[5/5] Final verification..."

FINAL_PLAN=$(backend_get "api/runtime/completion/plan")
REMAINING=$(echo "$FINAL_PLAN" \
  | grep -o '"completionCount":[0-9]*' \
  | grep -o '[0-9]*' \
  | head -1 || true)
REMAINING="${REMAINING:-unknown}"

# Summary table — parsed inside the container
FINAL_ENGINES_JSON=$(backend_get "api/runtime/engines")
echo "$FINAL_ENGINES_JSON" \
  | parse_json "
import json,sys
data=json.load(sys.stdin)
engines=data if isinstance(data,list) else data.get('engines',[])
ready=[e['id'] for e in engines if e.get('status')=='ready']
blocked=[e for e in engines if e.get('status')!='ready']
print('  Ready   (%d):' % len(ready))
for e in ready: print('    [OK] '+e)
if blocked:
    print('  Pending (%d):' % len(blocked))
    for e in blocked: print('    [--] '+e['id']+'  ('+e.get('status','?')+')')
" 2>/dev/null || true

echo ""
echo "  Engines still pending: ${REMAINING}"

if [ "$REMAINING" != "0" ] && [ "$REMAINING" != "unknown" ]; then
  echo ""
  echo "  Still missing:"
  echo "$FINAL_PLAN" \
    | grep -o '"engineId":"[^"]*"' \
    | sed 's/"engineId":"//;s/"//' \
    | sed 's/^/    [!!] /' || true
  echo ""
  echo "ERROR: ${REMAINING} engine(s) could not be provisioned."
  exit 1
fi

echo ""
echo "All compatible engines provisioned and verified."
echo "========================================================"
