#!/usr/bin/env bash
# Install Piper TTS lightweight wrapper.
set -euo pipefail

MODELS_ROOT="${MODELS_ROOT:-/models}"
log() { printf '[install-piper] %s\n' "$*"; }

mkdir -p "${MODELS_ROOT}/piper"
touch "${MODELS_ROOT}/.installed-piper"
log "Piper model directory ready (wrapper already installed in image)."
