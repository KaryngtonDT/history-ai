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
echo "Runtime readiness:"
if curl -sf "${API_BASE}/api/runtime/readiness" >/dev/null; then
  echo "  Runtime API  OK"
  READY_COUNT="$(curl -sf "${API_BASE}/api/runtime/readiness" | python -c "import sys,json; d=json.load(sys.stdin); print(d.get('readyCount',0))" 2>/dev/null || echo "?")"
  TOTAL_COUNT="$(curl -sf "${API_BASE}/api/runtime/readiness" | python -c "import sys,json; d=json.load(sys.stdin); print(d.get('totalCount',0))" 2>/dev/null || echo "?")"
  echo "  Engines ready  ${READY_COUNT}/${TOTAL_COUNT}"
else
  echo "  Runtime API  FAIL"
  FAIL=1
fi

echo ""
echo "Runtime kernel (video pipeline):"
for capability in speech_to_text translation text_to_speech voice_clone lip_sync video_render; do
  VIEW="$(curl -sf "${API_BASE}/api/runtime/capabilities/${capability}/selection-view" 2>/dev/null || echo "")"
  if [ -z "${VIEW}" ]; then
    echo "  ${capability}  FAIL"
    FAIL=1
    continue
  fi
  ENGINE="$(echo "${VIEW}" | python -c "import sys,json; d=json.load(sys.stdin); print(d.get('currentEngineId','?'))" 2>/dev/null || echo "?")"
  STATUS="$(echo "${VIEW}" | python -c "import sys,json; d=json.load(sys.stdin); r=d.get('resolvedEngine',{}); print('READY' if r.get('executable') else 'BLOCKED')" 2>/dev/null || echo "?")"
  echo "  ${capability}  ${STATUS} (${ENGINE})"
done

echo ""
echo "Production Readiness:"
curl -sf "${API_BASE}/api/platform/readiness" || { echo "FAIL: readiness API"; exit 1; }
echo ""
echo "========================================================="

exit "${FAIL}"
