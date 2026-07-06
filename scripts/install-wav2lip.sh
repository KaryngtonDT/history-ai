#!/usr/bin/env bash
# Wav2Lip installer for Lumen Runtime — split root/system vs www-data/runtime phases.
# Official repo: https://github.com/Rudrabha/Wav2Lip
#
# Usage:
#   install-wav2lip.sh                 # root: system + runtime; non-root: runtime only
#   install-wav2lip.sh --system-only   # root: apt (if needed), dirs, wrapper, ownership
#   install-wav2lip.sh --runtime-only  # venv, pinned wheels, checkpoint, smoke test
set -euo pipefail

MODELS_ROOT="${MODELS_ROOT:-/models}"
RUNTIME_USER="${RUNTIME_USER:-www-data}"
VENVS="${MODELS_ROOT}/venvs"
SRC="${MODELS_ROOT}/src"
WAV2LIP_DIR="${SRC}/Wav2Lip"
VENV="${VENVS}/wav2lip"
CHECKPOINT_DIR="${MODELS_ROOT}/wav2lip"
CHECKPOINT="${CHECKPOINT_DIR}/wav2lip_gan.pth"
SYSTEM_MARKER="${CHECKPOINT_DIR}/.system-ready"
INSTALLED_MARKER="${CHECKPOINT_DIR}/.installed"
FAILED_MARKER="${CHECKPOINT_DIR}/.download-failed"
MIN_CHECKPOINT_BYTES=$((100 * 1024 * 1024))
OFFICIAL_REPO="https://github.com/Rudrabha/Wav2Lip.git"

# Pinned wheels — avoid compiling numpy from source (Wav2Lip requirements.txt is legacy).
TORCH_VERSION="2.4.1"
TORCHVISION_VERSION="0.19.1"
NUMPY_VERSION="1.26.4"
OPENCV_VERSION="4.10.0.84"
LIBROSA_VERSION="0.10.2.post1"
SCIPY_VERSION="1.11.4"
NUMBA_VERSION="0.60.0"
Tqdm_VERSION="4.66.5"

log() { printf '[install-wav2lip] %s\n' "$*"; }
die() { log "ERROR: $*"; exit 1; }
mark_download_failed() {
  local reason="$1"
  mkdir -p "${CHECKPOINT_DIR}"
  printf '%s\n' "${reason}" > "${FAILED_MARKER}"
  log "MODEL_DOWNLOAD_FAILED: ${reason}"
  exit 1
}

torch_index() {
  if command -v nvidia-smi >/dev/null 2>&1 && nvidia-smi >/dev/null 2>&1; then
    echo "https://download.pytorch.org/whl/cu124"
  else
    echo "https://download.pytorch.org/whl/cpu"
  fi
}

is_root() { [[ "${EUID:-$(id -u)}" -eq 0 ]]; }

need_system_packages() {
  command -v git >/dev/null 2>&1 \
    && command -v curl >/dev/null 2>&1 \
    && command -v wget >/dev/null 2>&1 \
    && command -v ffmpeg >/dev/null 2>&1 \
    && python3 -m venv --help >/dev/null 2>&1
}

system_phase() {
  if ! is_root; then
    log "System phase skipped (not root). Image must provide git, curl, wget, ffmpeg, python3-venv."
    return 0
  fi

  log "=== System phase (root) ==="

  if ! need_system_packages; then
    log "Installing system packages via apt..."
    apt-get update -qq
    DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
      git curl wget ca-certificates python3 python3-venv python3-pip \
      libsndfile1 ffmpeg libgl1 libglib2.0-0 \
      >/dev/null
  else
    log "System packages already present — skipping apt."
  fi

  mkdir -p "${CHECKPOINT_DIR}" "${SRC}" "${VENVS}"
  chown -R "${RUNTIME_USER}:${RUNTIME_USER}" "${CHECKPOINT_DIR}" "${SRC}" "${VENVS}" 2>/dev/null || true

  if [[ ! -x /usr/local/bin/wav2lip ]] || ! grep -q 'wav2lip_runner.py' /usr/local/bin/wav2lip 2>/dev/null; then
    log "Installing /usr/local/bin/wav2lip wrapper..."
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
  fi

  touch "${SYSTEM_MARKER}"
  chown "${RUNTIME_USER}:${RUNTIME_USER}" "${SYSTEM_MARKER}" 2>/dev/null || true
  log "System phase complete."
}

checkpoint_valid() {
  [[ -f "${CHECKPOINT}" ]] && [[ "$(wc -c < "${CHECKPOINT}" | tr -d ' ')" -ge "${MIN_CHECKPOINT_BYTES}" ]]
}

download_checkpoint() {
  if checkpoint_valid; then
    log "Checkpoint already present ($(du -h "${CHECKPOINT}" | awk '{print $1}'))."
    return 0
  fi

  mkdir -p "${CHECKPOINT_DIR}"
  local tmp="${CHECKPOINT}.partial"
  rm -f "${tmp}"

  # Official Google Drive / GitHub links are often expired (see Rudrabha/Wav2Lip#752).
  local urls=(
    "https://huggingface.co/Nekochu/Wav2Lip/resolve/main/wav2lip_gan.pth"
    "https://huggingface.co/numz/wav2lip_studio/resolve/main/Wav2lip/wav2lip_gan.pth"
    "https://huggingface.co/Non-playing-Character/Wav2Lip/resolve/main/wav2lip_gan.pth"
  )

  for url in "${urls[@]}"; do
    log "Trying checkpoint URL: ${url}"
    if curl -fsSL --connect-timeout 60 --max-time 7200 -o "${tmp}" "${url}" \
      || wget -q --timeout=60 -O "${tmp}" "${url}"; then
      if [[ "$(wc -c < "${tmp}" | tr -d ' ')" -ge "${MIN_CHECKPOINT_BYTES}" ]]; then
        mv "${tmp}" "${CHECKPOINT}"
        log "Checkpoint downloaded successfully."
        return 0
      fi
      log "Download too small — trying next mirror."
      rm -f "${tmp}"
    else
      log "Download failed — trying next mirror."
      rm -f "${tmp}"
    fi
  done

  if command -v huggingface-cli >/dev/null 2>&1; then
    log "Trying huggingface-cli download (Nekochu/Wav2Lip)..."
    rm -f "${tmp}"
    if huggingface-cli download Nekochu/Wav2Lip wav2lip_gan.pth --local-dir "${CHECKPOINT_DIR}" --local-dir-use-symlinks False 2>/dev/null; then
      if [[ -f "${CHECKPOINT_DIR}/wav2lip_gan.pth" ]] && checkpoint_valid; then
        log "Checkpoint downloaded via huggingface-cli."
        return 0
      fi
    fi
  elif python3 -c "import huggingface_hub" 2>/dev/null; then
    log "Trying huggingface_hub Python download (Nekochu/Wav2Lip)..."
    if CHECKPOINT="${CHECKPOINT}" python3 - <<'PY'
from huggingface_hub import hf_hub_download
import os, shutil
dest = os.environ["CHECKPOINT"]
path = hf_hub_download(repo_id="Nekochu/Wav2Lip", filename="wav2lip_gan.pth")
shutil.copy2(path, dest)
PY
    then
      if checkpoint_valid; then
        log "Checkpoint downloaded via huggingface_hub."
        return 0
      fi
    fi
  fi

  mark_download_failed "All checkpoint mirrors failed. Manual download: see docs/operations/WAV2LIP_INSTALLATION.md"
}

install_python_env() {
  log "=== Runtime phase (Python + venv) ==="

  mkdir -p "${CHECKPOINT_DIR}" "${SRC}" "${VENVS}"

  if [[ ! -d "${WAV2LIP_DIR}/.git" ]]; then
    log "Cloning official Wav2Lip repository..."
    rm -rf "${WAV2LIP_DIR}"
    git clone --depth 1 "${OFFICIAL_REPO}" "${WAV2LIP_DIR}"
  else
    log "Wav2Lip source already cloned."
  fi

  if [[ ! -x "${VENV}/bin/python" ]]; then
    log "Creating Python venv at ${VENV}..."
    python3 -m venv "${VENV}"
  fi

  # shellcheck disable=SC1091
  source "${VENV}/bin/activate"
  pip install -q --upgrade "pip>=24" wheel "setuptools<82"

  local index
  index="$(torch_index)"
  log "Installing pinned PyTorch (${TORCH_VERSION}) from ${index}..."
  pip install -q "torch==${TORCH_VERSION}" "torchvision==${TORCHVISION_VERSION}" --index-url "${index}"

  log "Installing pinned runtime dependencies (wheels only)..."
  pip install -q \
    "numpy==${NUMPY_VERSION}" \
    "opencv-python-headless==${OPENCV_VERSION}" \
    "librosa==${LIBROSA_VERSION}" \
    "scipy==${SCIPY_VERSION}" \
    "numba==${NUMBA_VERSION}" \
    "tqdm==${Tqdm_VERSION}"

  deactivate
}

smoke_test() {
  log "Running minimal import smoke test..."
  "${VENV}/bin/python" - <<'PY'
import torch
import cv2
import librosa
import numpy
print("imports_ok", torch.__version__, numpy.__version__)
PY

  if [[ -x /usr/local/bin/wav2lip ]]; then
    /usr/local/bin/wav2lip --help >/dev/null 2>&1 || true
  fi
}

runtime_phase() {
  if is_root && id "${RUNTIME_USER}" >/dev/null 2>&1; then
    log "Dropping privileges to ${RUNTIME_USER} for runtime phase..."
    exec runuser -u "${RUNTIME_USER}" -- env MODELS_ROOT="${MODELS_ROOT}" bash "$0" --runtime-only
  fi

  log "=== Runtime phase (${USER:-unknown}) ==="
  rm -f "${FAILED_MARKER}"

  install_python_env
  download_checkpoint

  if ! checkpoint_valid; then
    mark_download_failed "Checkpoint missing or too small after download."
  fi

  smoke_test

  rm -f "${FAILED_MARKER}"
  touch "${INSTALLED_MARKER}"
  log "Wav2Lip runtime provisioning complete."
}

usage() {
  cat <<EOF
Usage: install-wav2lip.sh [--system-only | --runtime-only]
EOF
}

main() {
  local mode="${1:-all}"
  case "${mode}" in
    --system-only)
      system_phase
      ;;
    --runtime-only)
      runtime_phase
      ;;
    all|--all|"")
      if is_root; then
        system_phase
        runtime_phase
      else
        runtime_phase
      fi
      ;;
    -h|--help)
      usage
      ;;
    *)
      die "Unknown option: ${mode}"
      ;;
  esac
}

main "${1:-all}"
