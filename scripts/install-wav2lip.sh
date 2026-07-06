#!/usr/bin/env bash
# Install Wav2Lip (CPU-friendly lip sync) into /models — official repo + checkpoint only.
set -euo pipefail

MODELS_ROOT="${MODELS_ROOT:-/models}"
VENVS="${MODELS_ROOT}/venvs"
SRC="${MODELS_ROOT}/src"
WAV2LIP_DIR="${SRC}/Wav2Lip"
VENV="${VENVS}/wav2lip"
CHECKPOINT="${MODELS_ROOT}/wav2lip/wav2lip_gan.pth"

log() { printf '[install-wav2lip] %s\n' "$*"; }
die() { log "ERROR: $*"; exit 1; }

torch_index() {
  if command -v nvidia-smi >/dev/null 2>&1 && nvidia-smi >/dev/null 2>&1; then
    echo "https://download.pytorch.org/whl/cu124"
  else
    echo "https://download.pytorch.org/whl/cpu"
  fi
}

ensure_packages() {
  apt-get update -qq
  DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
    git curl wget ca-certificates python3-venv python3-dev build-essential \
    libsndfile1 ffmpeg libgl1 libglib2.0-0 \
    >/dev/null 2>&1 || true
}

install_wav2lip() {
  log "=== Installing Wav2Lip ==="
  mkdir -p "${MODELS_ROOT}/wav2lip" "${SRC}" "${VENVS}"

  if [[ ! -d "${WAV2LIP_DIR}/.git" ]]; then
    log "Cloning official Wav2Lip repository..."
    rm -rf "${WAV2LIP_DIR}"
    git clone --depth 1 https://github.com/Rudrabha/Wav2Lip.git "${WAV2LIP_DIR}"
  fi

  python3 -m venv "${VENV}"
  # shellcheck disable=SC1091
  source "${VENV}/bin/activate"
  pip install -q --upgrade pip wheel
  pip install -q torch torchvision torchaudio --index-url "$(torch_index)"
  pip install -q -r "${WAV2LIP_DIR}/requirements.txt" || pip install -q librosa opencv-python-headless tqdm numba

  if [[ ! -f "${CHECKPOINT}" ]]; then
    log "Downloading wav2lip_gan.pth checkpoint..."
    mkdir -p "$(dirname "${CHECKPOINT}")"
    wget -q -O "${CHECKPOINT}" \
      "https://github.com/Rudrabha/Wav2Lip/releases/download/v0.1/wav2lip_gan.pth" \
      || die "Checkpoint download failed"
  fi

  cat > /usr/local/bin/wav2lip <<'WRAP'
#!/bin/bash
set -euo pipefail
VENV_PYTHON="/models/venvs/wav2lip/bin/python"
RUNNER="/opt/lumen/engines/wav2lip_runner.py"
if [[ -x "${VENV_PYTHON}" && -f "${RUNNER}" && -f "/models/wav2lip/wav2lip_gan.pth" ]]; then
  export WAV2LIP_SRC="/models/src/Wav2Lip"
  exec "${VENV_PYTHON}" "${RUNNER}" "$@"
fi
exec /opt/lumen/placeholders/wav2lip "$@"
WRAP
  chmod +x /usr/local/bin/wav2lip
  touch "${MODELS_ROOT}/.installed-wav2lip"
  log "Wav2Lip installed."
}

ensure_packages
install_wav2lip
