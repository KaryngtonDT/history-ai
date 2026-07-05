#!/usr/bin/env bash
# Pull default Ollama model and verify pipeline engine binaries.
set -euo pipefail

COMPOSE_FILE="${COMPOSE_FILE:-docker-compose.prod-like.yml}"
COMPOSE=(docker compose -f "${COMPOSE_FILE}")
OLLAMA_MODEL="${OLLAMA_MODEL:-qwen2.5:3b}"

echo "========================================================="
echo "           LUMEN — SETUP DEFAULT AI ENGINES"
echo "========================================================="

echo ""
echo "Waiting for Ollama..."
for _ in $(seq 1 60); do
  if "${COMPOSE[@]}" exec -T ollama ollama list >/dev/null 2>&1; then
    break
  fi
  sleep 2
done

echo "Pulling Ollama model: ${OLLAMA_MODEL}"
"${COMPOSE[@]}" exec -T ollama ollama pull "${OLLAMA_MODEL}"

echo ""
echo "Verifying backend engine binaries..."
"${COMPOSE[@]}" exec -T backend sh -c '
  set -e
  for bin in faster-whisper f5-tts openvoice latentsync ffmpeg; do
    if command -v "$bin" >/dev/null 2>&1; then
      echo "  OK  $bin"
    else
      echo "  FAIL $bin (missing)"
      exit 1
    fi
  done
'

echo ""
echo "Checking Ollama API from backend..."
"${COMPOSE[@]}" exec -T backend sh -c \
  'wget -q -O - "${OLLAMA_BASE_URL:-http://ollama:11434}/api/tags" | grep -q models'

echo ""
echo "Default engines are installed and configured."
echo "  STT: faster_whisper (model: ${STT_FASTER_WHISPER_MODEL:-base})"
echo "  Translation: ollama (${OLLAMA_MODEL})"
echo "  TTS / Voice / Lip sync: CLI shims (mount real weights under ./models/)"
echo "========================================================="
