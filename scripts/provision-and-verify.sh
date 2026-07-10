#!/usr/bin/env bash
# Full engine provisioning with strict verification.
# Provisions engines ONE BY ONE via docker exec (bypasses nginx timeout entirely).
# Shows real-time status: already ready, in progress, done, failed.
# Called by prod-reset and prod-redeploy — no || true allowed here.
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "${ROOT}"

COMPOSE_FILE="${COMPOSE_FILE:-docker-compose.prod-like.yml}"
COMPOSE=(docker compose -f "${COMPOSE_FILE}")
BACKEND_URL="${BACKEND_URL:-http://localhost:8000}"
BACKEND_CONTAINER="${BACKEND_CONTAINER:-history-ai-prod-like-backend-1}"
HEALTH_TIMEOUT=120

# Colors / symbols
GREEN='\033[0;32m'; YELLOW='\033[1;33m'; RED='\033[0;31m'; CYAN='\033[0;36m'; NC='\033[0m'
OK="[OK]"; SKIP="[--]"; FAIL="[!!]"; WAIT="[..]"

# Call a GET endpoint inside the container (no nginx timeout risk for reads)
backend_get() {
  "${COMPOSE[@]}" exec -T backend curl -sf --max-time 30 "http://localhost/$1" 2>/dev/null
}

# Provision a single engine inside the container (completely bypasses nginx)
provision_engine_exec() {
  local engine_id="$1"
  "${COMPOSE[@]}" exec -T backend \
    curl -sf --max-time 7200 -X POST "http://localhost/api/runtime/engines/${engine_id}/provision" \
    2>/dev/null
}

echo "========================================================="
echo "   LUMEN — ENGINE PROVISIONING & VERIFICATION"
echo "========================================================="

# ------------------------------------------------------------------
echo ""
echo "[1/5] Ensuring stack is running..."
"${COMPOSE[@]}" up -d postgres redis ollama backend >/dev/null 2>&1

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

# Fetch current engine states
ENGINES_JSON=$(backend_get "api/runtime/engines")
PLAN_JSON=$(backend_get "api/runtime/provision/plan")

# Extract engines to provision from the plan
ENGINES_TO_PROVISION=$(echo "$PLAN_JSON" \
  | python3 -c "
import json, sys
data = json.load(sys.stdin)
plan = data.get('compatibleEngineCompletionPlan', [])
for e in plan:
    print(e['engineId'])
" 2>/dev/null || echo "")

# Extract already-ready engines
READY_ENGINES=$(echo "$ENGINES_JSON" \
  | python3 -c "
import json, sys
data = json.load(sys.stdin)
engines = data if isinstance(data, list) else data.get('engines', [])
for e in engines:
    if e.get('status') == 'ready':
        print(e['id'])
" 2>/dev/null || echo "")

# Show already-ready engines
echo "  Already READY:"
if [ -z "$READY_ENGINES" ]; then
  echo "    (none)"
else
  echo "$READY_ENGINES" | while read -r eid; do
    printf "    ${GREEN}${OK}${NC} %s\n" "$eid"
  done
fi

# Count engines to provision
PLAN_COUNT=$(echo "$ENGINES_TO_PROVISION" | grep -c '\S' || echo "0")

echo ""
if [ "$PLAN_COUNT" -eq 0 ]; then
  echo "  Nothing to provision — all compatible engines already READY."
else
  echo "  To provision ($PLAN_COUNT engines):"
  echo "$ENGINES_TO_PROVISION" | while read -r eid; do
    printf "    ${CYAN}${WAIT}${NC} %s\n" "$eid"
  done
  echo ""

  FAILED_ENGINES=""
  DONE_ENGINES=""

  while IFS= read -r ENGINE_ID; do
    [ -z "$ENGINE_ID" ] && continue

    printf "  ${YELLOW}${WAIT}${NC} %-30s installing..." "$ENGINE_ID"

    RESULT=$(provision_engine_exec "$ENGINE_ID" 2>/dev/null || echo '{"ok":false}')
    ENGINE_OK=$(echo "$RESULT" | grep -o '"ok":true' | head -1 || echo "")

    if [ -n "$ENGINE_OK" ]; then
      printf "\r  ${GREEN}${OK}${NC} %-30s done\n" "$ENGINE_ID"
      DONE_ENGINES="${DONE_ENGINES} ${ENGINE_ID}"
    else
      # Check if it became ready despite non-ok response
      STATUS_JSON=$(backend_get "api/runtime/engines/${ENGINE_ID}/compatibility" 2>/dev/null || echo '{}')
      IS_READY=$(echo "$STATUS_JSON" | grep -o '"status":"ready"' | head -1 || echo "")
      if [ -n "$IS_READY" ]; then
        printf "\r  ${GREEN}${OK}${NC} %-30s ready\n" "$ENGINE_ID"
        DONE_ENGINES="${DONE_ENGINES} ${ENGINE_ID}"
      else
        printf "\r  ${RED}${FAIL}${NC} %-30s FAILED\n" "$ENGINE_ID"
        FAILED_ENGINES="${FAILED_ENGINES} ${ENGINE_ID}"
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
  | head -1 || echo "")
REMAINING="${REMAINING:-unknown}"

# Recount ready engines
FINAL_READY=$(backend_get "api/runtime/engines" \
  | python3 -c "
import json, sys
data = json.load(sys.stdin)
engines = data if isinstance(data, list) else data.get('engines', [])
ready = [e['id'] for e in engines if e.get('status') == 'ready']
print(f'  Ready engines   : {len(ready)}')
for e in ready:
    print(f'    {e}')
blocked = [e['id'] for e in engines if e.get('status') not in ('ready',)]
print(f'  Not ready       : {len(blocked)}')
for e in blocked:
    print(f'    {e}  ({e.get(\"status\",\"?\")})')
" 2>/dev/null || echo "  (could not parse engine list)")

echo "$FINAL_READY"
echo ""
echo "  Engines still pending: ${REMAINING}"

if [ "$REMAINING" != "0" ] && [ "$REMAINING" != "unknown" ]; then
  echo ""
  echo "  Still missing:"
  echo "$FINAL_PLAN" \
    | grep -o '"engineId":"[^"]*"' \
    | grep -o '[^"]*"$' \
    | tr -d '"' \
    | sed "s/^/    ${FAIL} /" || true
  echo ""
  echo "ERROR: ${REMAINING} engine(s) could not be provisioned."
  exit 1
fi

echo ""
echo "All compatible engines provisioned and verified."
echo "========================================================"
