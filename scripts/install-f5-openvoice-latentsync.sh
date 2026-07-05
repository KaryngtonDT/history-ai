#!/usr/bin/env bash
# Install F5-TTS, OpenVoice V2, and LatentSync into /models (venv + weights).
# Usage: install-gpu-engines.sh [--engine f5|openvoice|latentsync|all] [--rollback]
set -euo pipefail

MODELS_ROOT="${MODELS_ROOT:-/models}"
STORAGE_ROOT="${STORAGE_ROOT:-/var/www/html/storage}"
INSTALL_LOG="${STORAGE_ROOT}/runtime/install"
MANIFEST="${INSTALL_LOG}/manifest.json"
ROLLBACK_DIR="${INSTALL_LOG}/rollback"
VENVS="${MODELS_ROOT}/venvs"
SRC="${MODELS_ROOT}/src"
USE_GPU="${USE_GPU:-auto}"

log() { printf '[install-gpu-engines] %s\n' "$*"; }
die() { log "ERROR: $*"; exit 1; }

detect_gpu() {
  if [[ "${USE_GPU}" == "1" ]]; then return 0; fi
  if [[ "${USE_GPU}" == "0" ]]; then return 1; fi
  command -v nvidia-smi >/dev/null 2>&1 && nvidia-smi >/dev/null 2>&1
}

torch_index() {
  if detect_gpu && command -v nvidia-smi >/dev/null 2>&1; then
    echo "https://download.pytorch.org/whl/cu124"
  else
    echo "https://download.pytorch.org/whl/cpu"
  fi
}

ensure_base_packages() {
  apt-get update -qq
  DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
    git curl wget ca-certificates unzip \
    python3-venv python3-dev build-essential pkg-config \
    libsndfile1 libgl1 libglib2.0-0 \
    libavformat-dev libavcodec-dev libavdevice-dev libavutil-dev \
    libswscale-dev libswresample-dev libavfilter-dev \
    ffmpeg \
    >/dev/null
}

write_manifest() {
  mkdir -p "${INSTALL_LOG}"
  python3 - <<PY
import json, datetime, os
manifest = {
    "installedAt": datetime.datetime.utcnow().isoformat() + "Z",
    "engines": {}
}
for name in ("f5", "openvoice", "latentsync"):
    marker = os.path.join("${MODELS_ROOT}", ".installed-" + name)
    manifest["engines"][name] = os.path.isfile(marker)
path = "${MANIFEST}"
with open(path, "w") as f:
    json.dump(manifest, f, indent=2)
print("Wrote", path)
PY
}

mark_installed() {
  touch "${MODELS_ROOT}/.installed-${1}"
}

rollback() {
  log "Rolling back to placeholders..."
  mkdir -p "${ROLLBACK_DIR}"
  for bin in f5-tts openvoice latentsync; do
    if [[ -f "/usr/local/bin/${bin}" ]]; then
      cp "/usr/local/bin/${bin}" "${ROLLBACK_DIR}/${bin}.$(date +%s).bak" || true
    fi
  done
  rm -rf "${VENVS}/f5-tts" "${VENVS}/openvoice" "${VENVS}/latentsync"
  rm -f "${MODELS_ROOT}/.installed-f5" "${MODELS_ROOT}/.installed-openvoice" "${MODELS_ROOT}/.installed-latentsync"
  log "Rollback complete — restart backend container to restore image CLIs if needed."
  exit 0
}

download_f5_ref() {
  local ref_dir="${MODELS_ROOT}/f5/refs"
  mkdir -p "${ref_dir}"
  if [[ ! -f "${ref_dir}/default.wav" ]]; then
    log "Downloading F5 reference audio..."
    if ! curl -fsSL -o "${ref_dir}/default.wav" \
      "https://github.com/SWivid/F5-TTS/raw/main/src/f5_tts/infer/examples/basic/basic_ref_en.wav"; then
      die "Could not download F5 reference wav"
    fi
    echo "Some call it magic, some call it science." > "${ref_dir}/default.wav.txt"
  fi
}

install_f5() {
  log "=== Installing F5-TTS ==="
  local venv="${VENVS}/f5-tts"
  python3 -m venv "${venv}"
  # shellcheck disable=SC1091
  source "${venv}/bin/activate"
  pip install -q --upgrade pip wheel
  local idx
  idx="$(torch_index)"
  pip install -q torch torchaudio --index-url "${idx}"
  pip install -q f5-tts huggingface_hub
  mkdir -p "${MODELS_ROOT}/f5"
  if [[ ! -f "${MODELS_ROOT}/f5/.hf_done" ]]; then
    log "Downloading F5-TTS weights (HF)..."
    huggingface-cli download SWivid/F5-TTS --local-dir "${MODELS_ROOT}/f5/hf" --local-dir-use-symlinks False || true
    touch "${MODELS_ROOT}/f5/.hf_done"
  fi
  download_f5_ref
  deactivate
  mark_installed f5
  log "F5-TTS install done."
}

patch_openvoice_cpu() {
  local repo="${SRC}/OpenVoice"
  local target="${repo}/openvoice/se_extractor.py"
  [[ -f "${target}" ]] || return 0
  python3 - <<PY
from pathlib import Path
path = Path("${target}")
text = path.read_text()
text = text.replace('device="cuda", compute_type="float16"', 'device="cuda" if __import__("torch").cuda.is_available() else "cpu", compute_type="float16" if __import__("torch").cuda.is_available() else "float32"')
path.write_text(text)
print("Patched OpenVoice se_extractor for CPU fallback")
PY
}

install_openvoice() {
  log "=== Installing OpenVoice V2 ==="
  local venv="${VENVS}/openvoice"
  rm -rf "${venv}"
  mkdir -p "${SRC}" "${MODELS_ROOT}/openvoice"
  if [[ ! -d "${SRC}/OpenVoice/.git" ]]; then
    git clone --depth 1 https://github.com/myshell-ai/OpenVoice.git "${SRC}/OpenVoice"
  fi
  python3 -m venv "${venv}"
  # shellcheck disable=SC1091
  source "${venv}/bin/activate"
  pip install -q --upgrade pip wheel
  local idx
  idx="$(torch_index)"
  pip install -q torch torchaudio --index-url "${idx}"
  pip install -q "numpy>=1.24.4" scipy librosa pydub wavmark eng_to_ipa inflect unidecode \
    whisper-timestamped openai python-dotenv pypinyin cn2an jieba langid faster-whisper
  pip install -q "av==14.1.0"
  pip install -q --no-deps -e "${SRC}/OpenVoice"
  pip install -q --no-deps "git+https://github.com/myshell-ai/MeloTTS.git"
  pip install -q cn2an inflect unidic-lite pypinyin eng-to-ipa gruut langid jieba loguru anyascii \
    cached_path fugashi g2p_en g2pkk jamo mecab-python3 pykakasi transformers txtsplit num2words
  log "Skipping full UniDic download (526MB); unidic-lite covers EN. Run: python -m unidic download"
  patch_openvoice_cpu
  local ckpt="${MODELS_ROOT}/openvoice/checkpoints_v2"
  if [[ ! -d "${ckpt}/converter" ]]; then
    log "Downloading OpenVoice V2 checkpoints..."
    curl -fsSL -o /tmp/openvoice_v2.zip \
      "https://myshell-public-repo-host.s3.amazonaws.com/openvoice/checkpoints_v2_0417.zip"
    unzip -q -o /tmp/openvoice_v2.zip -d "${MODELS_ROOT}/openvoice"
    rm -f /tmp/openvoice_v2.zip
  fi
  mkdir -p "${MODELS_ROOT}/openvoice/refs"
  if [[ ! -f "${MODELS_ROOT}/openvoice/refs/default.wav" ]]; then
    cp "${MODELS_ROOT}/f5/refs/default.wav" "${MODELS_ROOT}/openvoice/refs/default.wav" 2>/dev/null \
      || ffmpeg -f lavfi -i "sine=frequency=440:duration=2" -y "${MODELS_ROOT}/openvoice/refs/default.wav" 2>/dev/null \
      || true
  fi
  deactivate
  mark_installed openvoice
  log "OpenVoice V2 install done."
}

install_latentsync() {
  log "=== Installing LatentSync ==="
  local venv="${VENVS}/latentsync"
  mkdir -p "${SRC}" "${MODELS_ROOT}/latentsync/checkpoints"
  if [[ ! -d "${SRC}/LatentSync/.git" ]]; then
    git clone --depth 1 https://github.com/bytedance/LatentSync.git "${SRC}/LatentSync"
  fi
  python3 -m venv "${venv}"
  # shellcheck disable=SC1091
  source "${venv}/bin/activate"
  pip install -q --upgrade pip wheel
  local idx="https://download.pytorch.org/whl/cu121"
  if ! detect_gpu; then
    idx="https://download.pytorch.org/whl/cpu"
    log "WARN: No GPU — LatentSync will install but inference needs CUDA."
  fi
  pip install -q torch torchvision torchaudio --index-url "${idx}"
  pip install -q -r "${SRC}/LatentSync/requirements.txt" || pip install -q huggingface_hub omegaconf diffusers transformers accelerate opencv-python-headless
  pip install -q huggingface_hub
  if [[ ! -f "${MODELS_ROOT}/latentsync/checkpoints/latentsync_unet.pt" ]]; then
    log "Downloading LatentSync checkpoints..."
    huggingface-cli download ByteDance/LatentSync-1.6 whisper/tiny.pt \
      --local-dir "${MODELS_ROOT}/latentsync/checkpoints" --local-dir-use-symlinks False
    huggingface-cli download ByteDance/LatentSync-1.6 latentsync_unet.pt \
      --local-dir "${MODELS_ROOT}/latentsync/checkpoints" --local-dir-use-symlinks False
  fi
  mkdir -p "${MODELS_ROOT}/latentsync/refs"
  if [[ ! -f "${MODELS_ROOT}/latentsync/refs/demo.mp4" ]]; then
    ffmpeg -f lavfi -i "color=c=black:s=320x240:d=2" -f lavfi -i "sine=frequency=440:duration=2" \
      -shortest -y "${MODELS_ROOT}/latentsync/refs/demo.mp4" 2>/dev/null || true
    ffmpeg -i "${MODELS_ROOT}/latentsync/refs/demo.mp4" -vn -y "${MODELS_ROOT}/latentsync/refs/demo.wav" 2>/dev/null || true
  fi
  deactivate
  mark_installed latentsync
  log "LatentSync install done."
}

ENGINE="all"
while [[ $# -gt 0 ]]; do
  case "$1" in
    --rollback) rollback ;;
    --engine) ENGINE="${2:-all}"; shift 2 ;;
    --engine=*) ENGINE="${1#*=}"; shift ;;
    *) shift ;;
  esac
done

mkdir -p "${VENVS}" "${SRC}" "${INSTALL_LOG}" "${ROLLBACK_DIR}"
ensure_base_packages

case "${ENGINE}" in
  f5) install_f5 ;;
  openvoice) install_openvoice ;;
  latentsync) install_latentsync ;;
  all)
    install_f5
    install_openvoice
    install_latentsync
    ;;
  *) die "Unknown engine: ${ENGINE}" ;;
esac

write_manifest
log "Complete. Run runtime tests: curl -X POST http://localhost:8000/api/runtime/engines/f5_tts/test"
