#!/usr/bin/env bash
# Install Whisper.cpp wrapper (CPU STT alternative). Full build optional — placeholder until models present.
set -euo pipefail

MODELS_ROOT="${MODELS_ROOT:-/models}"
log() { printf '[install-whisper-cpp] %s\n' "$*"; }

mkdir -p "${MODELS_ROOT}/whisper-cpp"
touch "${MODELS_ROOT}/.installed-whisper-cpp"
log "Whisper.cpp model directory ready (wrapper already installed in image)."
