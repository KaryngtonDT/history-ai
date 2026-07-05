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

check_engine() {
  local name="$1"
  local command="$2"
  if "${COMPOSE[@]}" exec -T backend sh -c "${command}" >/dev/null 2>&1; then
    echo "  ${name}  OK"
    return 0
  fi
  echo "  ${name}  FAIL"
  return 1
}

FAIL=0
check_endpoint "Health (/health)" "${API_BASE}/health" || FAIL=1
check_endpoint "Ready  (/ready)" "${API_BASE}/ready" || FAIL=1
check_endpoint "Live   (/live)" "${API_BASE}/live" || FAIL=1

echo ""
echo "Pipeline engines:"
check_engine "Faster Whisper" "command -v faster-whisper" || FAIL=1
check_engine "FFmpeg" "command -v ffmpeg" || FAIL=1
check_engine "F5-TTS CLI" "command -v f5-tts" || FAIL=1
check_engine "OpenVoice CLI" "command -v openvoice" || FAIL=1
check_engine "LatentSync CLI" "command -v latentsync" || FAIL=1

if curl -sf "http://localhost:11434/api/tags" >/dev/null 2>&1; then
  echo "  Ollama API  OK"
else
  echo "  Ollama API  FAIL (http://localhost:11434)"
  FAIL=1
fi

echo ""
echo "Production Readiness:"
curl -sf "${API_BASE}/api/platform/readiness" || { echo "FAIL: readiness API"; exit 1; }
echo ""
echo "========================================================="

exit "${FAIL}"
