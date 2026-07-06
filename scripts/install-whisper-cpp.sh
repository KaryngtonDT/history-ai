#!/usr/bin/env bash
# Install Whisper.cpp wrapper (CPU STT alternative). Full build optional — placeholder until models present.
set -euo pipefail

MODELS_ROOT="${MODELS_ROOT:-/models}"
log() { printf '[install-whisper-cpp] %s\n' "$*"; }

mkdir -p "${MODELS_ROOT}/whisper-cpp"

cat > /usr/local/bin/whisper-cpp <<'WRAP'
#!/bin/bash
set -euo pipefail
if [[ -x /models/venvs/whisper-cpp/bin/whisper-cpp ]]; then
  exec /models/venvs/whisper-cpp/bin/whisper-cpp "$@"
fi
exec /opt/lumen/placeholders/whisper-cpp "$@"
WRAP
chmod +x /usr/local/bin/whisper-cpp
touch "${MODELS_ROOT}/.installed-whisper-cpp"
log "Whisper.cpp CLI registered (placeholder until full build)."
