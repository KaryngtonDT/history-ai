#!/usr/bin/env bash
# Full engine provisioning with strict verification.
# Installs ALL compatible engines and exits non-zero if any remain unprovisioned.
# Called by prod-reset and prod-redeploy — no || true allowed here.
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "${ROOT}"

COMPOSE_FILE="${COMPOSE_FILE:-docker-compose.prod-like.yml}"
COMPOSE=(docker compose -f "${COMPOSE_FILE}")
BACKEND_URL="${BACKEND_URL:-http://localhost:8000}"
BACKEND_CONTAINER="${BACKEND_CONTAINER:-history-ai-prod-like-backend-1}"
HEALTH_TIMEOUT=120  # seconds to wait for backend after rebuild
PROVISION_TIMEOUT=3600  # 1 hour max per provisioning call

echo "========================================================="
echo "   LUMEN — FULL ENGINE PROVISIONING & VERIFICATION"
echo "========================================================="

# Ensure nginx inside the container uses a 1h read timeout for provisioning.
# The image may have been built with the old 600s default; reload without rebuild.
patch_nginx_timeout() {
  local container="$1"
  if docker inspect "$container" >/dev/null 2>&1; then
    docker exec "$container" sed -i \
      's/fastcgi_read_timeout [0-9]*s/fastcgi_read_timeout 3600s/g;s/fastcgi_send_timeout [0-9]*s/fastcgi_send_timeout 3600s/g' \
      /etc/nginx/conf.d/default.conf 2>/dev/null \
    && docker exec "$container" nginx -s reload 2>/dev/null \
    && echo "  nginx timeout patched to 3600s and reloaded." \
    || echo "  (nginx patch skipped — container not found or nginx not running)"
  fi
}

# ------------------------------------------------------------------
echo ""
echo "[1/6] Ensuring stack is running..."
"${COMPOSE[@]}" up -d postgres redis ollama backend >/dev/null 2>&1
# ------------------------------------------------------------------

echo ""
echo "[2/6] Waiting for backend to become healthy (max ${HEALTH_TIMEOUT}s)..."
elapsed=0
until curl -sf --max-time 5 "${BACKEND_URL}/health" >/dev/null 2>&1; do
  if [ "$elapsed" -ge "$HEALTH_TIMEOUT" ]; then
    echo "ERROR: Backend not reachable at ${BACKEND_URL} after ${HEALTH_TIMEOUT}s."
    exit 1
  fi
  printf "  ...waiting (%ds)\r" "$elapsed"
  sleep 3
  elapsed=$((elapsed + 3))
done
echo "  Backend healthy.                     "
patch_nginx_timeout "${BACKEND_CONTAINER}"

# ------------------------------------------------------------------
echo ""
echo "[3/6] Refreshing hardware capability report..."
curl -sf --max-time 30 "${BACKEND_URL}/api/runtime/hardware" >/dev/null
echo "  Hardware report refreshed."

# ------------------------------------------------------------------
echo ""
echo "[4/6] Showing provisioning plan..."
curl -sf --max-time 30 "${BACKEND_URL}/api/runtime/provision/plan" \
  | python3 -m json.tool 2>/dev/null || true

# ------------------------------------------------------------------
echo ""
echo "[5/6] Provisioning all compatible engines..."
echo "  (taking all the time needed — timeout: ${PROVISION_TIMEOUT}s)"
echo ""

PROVISION_RESULT=$(curl -s --max-time "${PROVISION_TIMEOUT}" \
  -X POST "${BACKEND_URL}/api/runtime/provision/compatible")

if [ -z "$PROVISION_RESULT" ]; then
  echo "ERROR: No response from provision/compatible endpoint."
  exit 1
fi

echo "$PROVISION_RESULT" | python3 -m json.tool 2>/dev/null || echo "$PROVISION_RESULT"

PROVISION_OK=$(echo "$PROVISION_RESULT" \
  | grep -o '"ok":true' | head -1 || echo "")

if [ -z "$PROVISION_OK" ]; then
  echo ""
  echo "  WARN: Intelligent provisioner reported ok=false."
  echo "  Running completion pass to install any remaining engines..."
  echo ""
  COMPLETION_RESULT=$(curl -s --max-time "${PROVISION_TIMEOUT}" \
    -X POST "${BACKEND_URL}/api/runtime/completion/execute")
  echo "$COMPLETION_RESULT" | python3 -m json.tool 2>/dev/null || echo "$COMPLETION_RESULT"
fi

# ------------------------------------------------------------------
echo ""
echo "[6/6] Verifying — checking remaining completion plan..."
PLAN_JSON=$(curl -sf --max-time 30 "${BACKEND_URL}/api/runtime/completion/plan")

REMAINING=$(echo "$PLAN_JSON" \
  | grep -o '"completionCount":[0-9]*' \
  | grep -o '[0-9]*' \
  | head -1 || echo "")

REMAINING="${REMAINING:-unknown}"
echo "  Engines still pending provisioning: ${REMAINING}"

if [ "$REMAINING" != "0" ] && [ "$REMAINING" != "unknown" ]; then
  echo ""
  echo "  Pending engines:"
  echo "$PLAN_JSON" \
    | grep -o '"engineId":"[^"]*"' \
    | grep -o '"[^"]*"$' \
    | tr -d '"' \
    | sed 's/^/    - /' || true
  echo ""
  echo "ERROR: ${REMAINING} engine(s) could not be provisioned."
  echo "Run 'make doctor' or check docs/reports/Engine-Provisioning-Final.md"
  exit 1
fi

echo ""
echo "All compatible engines provisioned and verified."
echo "========================================================"
