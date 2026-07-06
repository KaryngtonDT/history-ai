#!/usr/bin/env bash
# Provision all auto-installable Lumen AI engines, then validate runtime readiness.
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "${ROOT}"

COMPOSE_FILE="${COMPOSE_FILE:-docker-compose.prod-like.yml}"
COMPOSE=(docker compose -f "${COMPOSE_FILE}")
BACKEND_URL="${BACKEND_URL:-http://localhost:8000}"

OLLAMA_MODELS=(
  "${OLLAMA_MODEL:-gemma3:4b}"
  "qwen3:4b"
  "deepseek-r1:1.5b"
)

echo "========================================================="
echo "        LUMEN — PROVISION SUPPORTED AI ENGINES"
echo "========================================================="

echo ""
echo "[1/5] Ensuring prod-like stack is up..."
"${COMPOSE[@]}" up -d postgres redis ollama backend >/dev/null

echo ""
echo "[2/5] Creating model and storage directories..."
"${COMPOSE[@]}" exec -T backend sh -c 'mkdir -p \
  /models/whisper /models/parakeet /models/canary \
  /models/f5 /models/kokoro /models/dia \
  /models/openvoice /models/chatterbox /models/xtts \
  /models/latentsync /models/echomimic /models/wav2lip \
  /var/www/html/storage/runtime /var/www/html/storage/runtime/benchmark /var/www/html/storage/cache /var/www/html/storage/logs'

echo ""
echo "[3/5] Waiting for Ollama..."
for _ in $(seq 1 60); do
  if "${COMPOSE[@]}" exec -T ollama ollama list >/dev/null 2>&1; then
    break
  fi
  sleep 2
done

echo ""
echo "[4/5] Pulling Ollama models..."
for model in "${OLLAMA_MODELS[@]}"; do
  echo "  → ollama pull ${model}"
  "${COMPOSE[@]}" exec -T ollama ollama pull "${model}" || echo "  WARN: failed to pull ${model}"
done

echo ""
echo "Prefetching Faster Whisper large-v3 (HuggingFace cache)..."
"${COMPOSE[@]}" exec -T backend python3 -c \
  "from faster_whisper import WhisperModel; WhisperModel('large-v3', device='cpu', compute_type='int8')" \
  || echo "  WARN: Faster Whisper prefetch failed"

echo ""
echo "[5/5] Running backend provisioning + validation..."
if curl -sf "${BACKEND_URL}/health" >/dev/null; then
  curl -sf -X POST "${BACKEND_URL}/api/runtime/provision" | python3 -m json.tool 2>/dev/null || true
  echo ""
  curl -sf -X POST "${BACKEND_URL}/api/runtime/pipeline/validate" | python3 -m json.tool 2>/dev/null || true
else
  echo "  Backend not reachable at ${BACKEND_URL}; run validation manually: make runtime-validate"
fi

echo ""
echo "Provisioning complete. Open /settings/runtime and run Verify/Test."
echo "Docs: docs/operations/ENGINE_INSTALLATION.md"
echo "Report: docs/reports/Sprint70_4-Engine-Provisioning.md"
echo "========================================================="
