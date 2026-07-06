#!/usr/bin/env bash
# Install Piper TTS lightweight wrapper.
set -euo pipefail

MODELS_ROOT="${MODELS_ROOT:-/models}"
log() { printf '[install-piper] %s\n' "$*"; }

mkdir -p "${MODELS_ROOT}/piper"

cat > /usr/local/bin/piper <<'WRAP'
#!/bin/bash
set -euo pipefail
if [[ -x /models/venvs/piper/bin/piper ]]; then
  exec /models/venvs/piper/bin/piper "$@"
fi
exec /opt/lumen/placeholders/piper "$@"
WRAP
chmod +x /usr/local/bin/piper
touch "${MODELS_ROOT}/.installed-piper"
log "Piper CLI registered (placeholder until voice models downloaded)."
