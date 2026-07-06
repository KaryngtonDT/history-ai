#!/usr/bin/env bash
# MuseTalk legacy install stub.
set -euo pipefail
MODELS_ROOT="${MODELS_ROOT:-/models}"
mkdir -p "${MODELS_ROOT}/musetalk"
printf '[install-musetalk] Legacy engine — prefer Wav2Lip on CPU-only hosts.\n'
exit 0
