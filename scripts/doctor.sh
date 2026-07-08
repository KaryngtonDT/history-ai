#!/usr/bin/env bash
# Lumen doctor — aggregate health and production readiness checks.
set -euo pipefail

API_BASE="${API_BASE:-http://localhost:8000}"
COMPOSE_FILE="${COMPOSE_FILE:-docker-compose.prod-like.yml}"
COMPOSE=(docker compose -f "${COMPOSE_FILE}")

echo "========================================================="
echo "                 LUMEN DOCTOR"
echo "========================================================="

check_endpoint() {
  local name="$1"
  local url="$2"
  if curl -sf "${url}" >/dev/null; then
    echo "  ${name}  OK"
    return 0
  fi
  echo "  ${name}  FAIL (${url})"
  return 1
}

FAIL=0
check_endpoint "Health (/health)" "${API_BASE}/health" || FAIL=1
check_endpoint "Ready  (/ready)" "${API_BASE}/ready" || FAIL=1
check_endpoint "Live   (/live)" "${API_BASE}/live" || FAIL=1

echo ""
echo "Runtime doctor (SSOT):"
DOCTOR="$(curl -sf "${API_BASE}/api/runtime/doctor" 2>/dev/null || echo "")"
if [ -z "${DOCTOR}" ]; then
  echo "  Runtime doctor API  FAIL"
  FAIL=1
else
  echo "  Runtime doctor API  OK"
  CORE_STATUS="$(echo "${DOCTOR}" | python -c "import sys,json; d=json.load(sys.stdin); print(d.get('coreStatus','unknown'))" 2>/dev/null || echo "?")"
  READY_COUNT="$(echo "${DOCTOR}" | python -c "import sys,json; d=json.load(sys.stdin); print(d.get('readyCount',0))" 2>/dev/null || echo "?")"
  TOTAL_COUNT="$(echo "${DOCTOR}" | python -c "import sys,json; d=json.load(sys.stdin); print(d.get('totalCount',0))" 2>/dev/null || echo "?")"
  echo "  Runtime Core      ${CORE_STATUS}"
  echo "  Engines ready     ${READY_COUNT}/${TOTAL_COUNT}"
  echo ""
  echo "Capabilities (by classification):"
  echo "${DOCTOR}" | python -c "
import sys, json
data = json.load(sys.stdin)
for cap in data.get('capabilities', []):
    cls = cap.get('classificationLabel') or cap.get('classification') or '?'
    avail = cap.get('availabilityLabel') or ('READY' if cap.get('executable') else 'UNAVAILABLE')
    print(f\"  [{cls:12}] {cap.get('capability','?'):16}  {avail}\")
" 2>/dev/null || FAIL=1
  if [ "${CORE_STATUS}" != "ready" ]; then
    FAIL=1
  fi
fi

echo ""
echo "Production Readiness:"
curl -sf "${API_BASE}/api/platform/readiness" || { echo "FAIL: readiness API"; exit 1; }
echo ""
echo "========================================================="

exit "${FAIL}"
