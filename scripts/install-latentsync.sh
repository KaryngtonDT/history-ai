#!/usr/bin/env bash
# Strict LatentSync provisioner for Lumen Runtime.
# Official repo: https://github.com/bytedance/LatentSync
# Official weights: https://huggingface.co/ByteDance/LatentSync-1.6
#
# Usage: install-latentsync.sh [--preflight-only] [--rollback]
# Aborts (exit 2) if GPU/CUDA is not available — never installs CPU-only silently.
set -euo pipefail

MODELS_ROOT="${MODELS_ROOT:-/models}"
STORAGE_ROOT="${STORAGE_ROOT:-/var/www/html/storage}"
INSTALL_LOG="${STORAGE_ROOT}/runtime/install"
REPORT="${INSTALL_LOG}/latentsync-report.json"
VENVS="${MODELS_ROOT}/venvs"
SRC="${MODELS_ROOT}/src"
VEN="${VENVS}/latentsync"
REPO="${SRC}/LatentSync"
OFFICIAL_REPO="https://github.com/bytedance/LatentSync.git"
OFFICIAL_HF="ByteDance/LatentSync-1.6"
LATENTSYNC_PYTHON="${LATENTSYNC_PYTHON:-3.10.13}"

log() { printf '[install-latentsync] %s\n' "$*"; }
die() { log "ERROR: $*"; exit 1; }
blocked() { log "BLOCKED: $*"; write_report "blocked" "$*"; exit 2; }

write_report() {
  local status="${1:-blocked}"
  local reason="${2:-}"
  mkdir -p "${INSTALL_LOG}"
  python3 - <<PY
import json, datetime, os, subprocess

def sh(cmd):
    try:
        return subprocess.check_output(cmd, shell=True, text=True, stderr=subprocess.STDOUT).strip()
    except Exception:
        return ""

repo_commit = ""
if os.path.isdir("${REPO}/.git"):
    repo_commit = sh("git -C ${REPO} rev-parse HEAD")

report = {
    "engine": "latentsync",
    "status": "${status}",
    "blockedReason": """${reason}""",
    "recordedAt": datetime.datetime.utcnow().isoformat() + "Z",
    "officialRepo": "${OFFICIAL_REPO}",
    "officialHuggingFace": "https://huggingface.co/${OFFICIAL_HF}",
    "repoCommit": repo_commit,
    "license": "Apache-2.0 (see upstream LICENSE)",
    "version": "LatentSync-1.6",
    "venv": "${VEN}",
    "modelsPath": "${MODELS_ROOT}/latentsync",
    "nvidiaSmi": sh("nvidia-smi --query-gpu=name,driver_version,memory.total --format=csv,noheader 2>/dev/null || echo unavailable"),
    "cudaAvailable": None,
    "minVramGb": 18,
}
try:
    import torch
    report["cudaAvailable"] = bool(torch.cuda.is_available())
    if torch.cuda.is_available():
        report["gpuName"] = torch.cuda.get_device_name(0)
        report["vramGb"] = round(torch.cuda.get_device_properties(0).total_memory / (1024**3), 2)
except Exception as exc:
    report["torchProbeError"] = str(exc)

with open("${REPORT}", "w") as f:
    json.dump(report, f, indent=2)
print("Wrote", "${REPORT}")
PY
}

preflight_gpu() {
  log "=== LatentSync preflight (GPU required) ==="
  if ! command -v nvidia-smi >/dev/null 2>&1; then
    blocked "nvidia-smi not found in container. LatentSync requires NVIDIA GPU + CUDA. Enable GPU passthrough in Docker (deploy.resources.reservations.devices) and install NVIDIA Container Toolkit on the host."
  fi
  if ! nvidia-smi >/dev/null 2>&1; then
    blocked "nvidia-smi failed. Driver or GPU not visible inside container."
  fi
  nvidia-smi --query-gpu=name,driver_version,memory.total,memory.free --format=csv
  local vram_mb
  vram_mb="$(nvidia-smi --query-gpu=memory.total --format=csv,noheader,nounits | head -1 | tr -d ' ')"
  if [[ -n "${vram_mb}" && "${vram_mb}" -lt 18000 ]]; then
    blocked "GPU VRAM ${vram_mb} MB < 18 GB minimum for LatentSync 1.6 inference (official README)."
  fi
}

preflight_ram() {
  local mem_avail_kb
  mem_avail_kb="$(awk '/MemAvailable:/ {print $2}' /proc/meminfo 2>/dev/null || echo 0)"
  if [[ "${mem_avail_kb}" -gt 0 && "${mem_avail_kb}" -lt 8000000 ]]; then
    blocked "Less than ~8 GB RAM available (${mem_avail_kb} kB). pip install torch+deps needs headroom; prior failure was exit 137 (OOM kill during pip)."
  fi
}

clone_official_repo() {
  mkdir -p "${SRC}"
  if [[ ! -d "${REPO}/.git" ]]; then
    log "Cloning official LatentSync..."
    git clone --depth 1 "${OFFICIAL_REPO}" "${REPO}"
  fi
  log "Repo commit: $(git -C "${REPO}" rev-parse HEAD)"
  log "Repo date:   $(git -C "${REPO}" log -1 --format=%ci)"
}

verify_cuda_in_venv() {
  # shellcheck disable=SC1091
  source "${VEN}/bin/activate"
  python3 - <<'PY'
import sys
import torch
if not torch.cuda.is_available():
    print("torch.cuda.is_available() is False after cu121 install", file=sys.stderr)
    sys.exit(1)
print("CUDA OK:", torch.cuda.get_device_name(0), "torch", torch.__version__, "cuda", torch.version.cuda)
PY
  deactivate
}

install_venv() {
  log "=== Creating isolated venv ${VEN} ==="
  rm -rf "${VEN}"
  python3 -m venv "${VEN}"
  # shellcheck disable=SC1091
  source "${VEN}/bin/activate"
  export PIP_NO_CACHE_DIR=1
  pip install -q --upgrade pip wheel
  log "Installing PyTorch 2.5.1+cu121 (staged — avoids OOM from full requirements.txt at once)..."
  pip install -q torch==2.5.1 torchvision==0.20.1 torchaudio==2.5.1 \
    --index-url https://download.pytorch.org/whl/cu121
  deactivate
  verify_cuda_in_venv
  # shellcheck disable=SC1091
  source "${VEN}/bin/activate"
  log "Installing LatentSync requirements.txt (official pins)..."
  pip install -q -r "${REPO}/requirements.txt"
  pip install -q huggingface_hub
  deactivate
}

download_official_weights() {
  local ckpt_dir="${MODELS_ROOT}/latentsync/checkpoints"
  mkdir -p "${ckpt_dir}"
  # shellcheck disable=SC1091
  source "${VEN}/bin/activate"
  if [[ ! -f "${ckpt_dir}/latentsync_unet.pt" ]]; then
    log "Downloading official weights from HuggingFace ${OFFICIAL_HF}..."
    huggingface-cli download "${OFFICIAL_HF}" latentsync_unet.pt \
      --local-dir "${ckpt_dir}" --local-dir-use-symlinks False
  fi
  if [[ ! -f "${ckpt_dir}/whisper/tiny.pt" ]]; then
    huggingface-cli download "${OFFICIAL_HF}" whisper/tiny.pt \
      --local-dir "${ckpt_dir}" --local-dir-use-symlinks False
  fi
  deactivate
  [[ -f "${ckpt_dir}/latentsync_unet.pt" ]] || die "Missing latentsync_unet.pt"
  [[ -f "${ckpt_dir}/whisper/tiny.pt" ]] || die "Missing whisper/tiny.pt"
  log "Weights OK: $(du -sh "${ckpt_dir}" | awk '{print $1}')"
}

ensure_ref_media() {
  mkdir -p "${MODELS_ROOT}/latentsync/refs"
  if [[ ! -f "${MODELS_ROOT}/latentsync/refs/demo.mp4" ]]; then
    ffmpeg -y -f lavfi -i "color=c=black:s=512x512:d=2" -f lavfi -i "sine=frequency=440:duration=2" \
      -shortest "${MODELS_ROOT}/latentsync/refs/demo.mp4" 2>/dev/null
    ffmpeg -y -i "${MODELS_ROOT}/latentsync/refs/demo.mp4" -vn \
      "${MODELS_ROOT}/latentsync/refs/demo.wav" 2>/dev/null
  fi
}

smoke_inference() {
  log "=== Real inference smoke test (no mock) ==="
  local out="/tmp/latentsync-smoke.mp4"
  rm -f "${out}"
  if ! latentsync \
    --video "${MODELS_ROOT}/latentsync/refs/demo.mp4" \
    --audio "${MODELS_ROOT}/latentsync/refs/demo.wav" \
    --model latentsync \
    --base-path "${MODELS_ROOT}/latentsync" \
    --output "${out}"; then
    blocked "Smoke inference failed — not marking installed."
  fi
  [[ -f "${out}" ]] || blocked "Smoke output missing: ${out}"
  local sz
  sz="$(wc -c < "${out}")"
  if [[ "${sz}" -lt 1000 ]]; then
    blocked "Smoke output too small (${sz} bytes) — inference did not produce valid video."
  fi
  log "Smoke OK: ${out} (${sz} bytes)"
}

rollback() {
  log "Rolling back LatentSync..."
  rm -rf "${VEN}" "${MODELS_ROOT}/.installed-latentsync"
  write_report "rolled_back" "user requested rollback"
  exit 0
}

while [[ $# -gt 0 ]]; do
  case "$1" in
    --rollback) rollback ;;
    --preflight-only) PREFLIGHT_ONLY=1; shift ;;
    *) shift ;;
  esac
done

mkdir -p "${INSTALL_LOG}"
preflight_gpu
preflight_ram
[[ "${PREFLIGHT_ONLY:-0}" == "1" ]] && { write_report "preflight_ok" ""; log "Preflight passed."; exit 0; }

clone_official_repo
install_venv
download_official_weights
ensure_ref_media
smoke_inference
touch "${MODELS_ROOT}/.installed-latentsync"
write_report "ready" ""
log "LatentSync installation complete and verified."
