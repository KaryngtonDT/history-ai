#!/usr/bin/env bash
# Lumen doctor — aggregate health and production readiness checks.
set -euo pipefail

API_BASE="${API_BASE:-http://localhost:8000}"

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
echo "Production Readiness:"
curl -sf "${API_BASE}/api/platform/readiness" || { echo "FAIL: readiness API"; exit 1; }
echo ""
echo "========================================================="

exit "${FAIL}"
