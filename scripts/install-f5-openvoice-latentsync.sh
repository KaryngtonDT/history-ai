#!/usr/bin/env bash
# Install F5-TTS, OpenVoice V2, and LatentSync into /models (venv + weights).
# Usage: install-gpu-engines.sh [--engine f5|openvoice|latentsync|all] [--lang-pack jp[,...]] [--rollback]
# Env: OPENVOICE_LANG_PACKS=jp  OPENVOICE_UNIDIC_FULL=1 (full 775MB lexicon instead of unidic-lite symlink)
set -euo pipefail

MODELS_ROOT="${MODELS_ROOT:-/models}"
STORAGE_ROOT="${STORAGE_ROOT:-/var/www/html/storage}"
INSTALL_LOG="${STORAGE_ROOT}/runtime/install"
MANIFEST="${INSTALL_LOG}/manifest.json"
ROLLBACK_DIR="${INSTALL_LOG}/rollback"
VENVS="${MODELS_ROOT}/venvs"
SRC="${MODELS_ROOT}/src"
USE_GPU="${USE_GPU:-auto}"
LANG_PACKS="${OPENVOICE_LANG_PACKS:-}"
UNIDIC_FULL="${OPENVOICE_UNIDIC_FULL:-0}"

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
  # torchaudio 2.9+ pulls torchcodec (broken in CPU-only Docker); pin 2.4.x for F5-TTS CLI
  pip install -q torch==2.4.1 torchaudio==2.4.1 --index-url "${idx}"
  pip install -q soundfile
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
text = text.replace(
    'device="cuda", compute_type="float16"',
    'device="cuda" if __import__("torch").cuda.is_available() else "cpu", compute_type="float16" if __import__("torch").cuda.is_available() else "float32"',
)
old = """    audio_segs = glob(f'{wavs_folder}/*.wav')
    if len(audio_segs) == 0:
        raise NotImplementedError('No audio segments found!')"""
new = """    audio_segs = glob(f'{wavs_folder}/*.wav')
    if len(audio_segs) == 0:
        import shutil
        os.makedirs(wavs_folder, exist_ok=True)
        fallback = os.path.join(wavs_folder, '000.wav')
        shutil.copy(audio_path, fallback)
        audio_segs = [fallback]"""
if old in text:
    text = text.replace(old, new)
    print("Patched OpenVoice se_extractor fallback for short clips")
else:
    print("OpenVoice se_extractor fallback already patched or layout changed")
path.write_text(text)
PY
}

lang_pack_enabled() {
  local pack="${1,,}"
  [[ ",${LANG_PACKS}," == *",${pack},"* ]]
}

patch_melotts_lazy_japanese() {
  local venv="${VENVS}/openvoice"
  "${venv}/bin/python" - <<'PY'
import importlib.util
import re
import sys

def patch_cleaner():
    spec = importlib.util.find_spec("melo.text.cleaner")
    if spec is None or spec.origin is None:
        raise SystemExit("melo.text.cleaner not found — install MeloTTS first")
    path = spec.origin
    text = open(path, encoding="utf-8").read()
    if "def _language_module(language):" in text:
        print("MeloTTS cleaner already patched for lazy JP import")
        return
    marker = "from . import chinese, japanese, english"
    if marker not in text:
        raise SystemExit(f"Unexpected melo/text/cleaner.py layout in {path}")
    patched = text.replace(
        "from . import chinese, japanese, english, chinese_mix, korean, french, spanish",
        "from . import chinese, english, chinese_mix, korean, french, spanish",
    ).replace(
        'language_module_map = {"ZH": chinese, "JP": japanese, "EN": english, \'ZH_MIX_EN\': chinese_mix, \'KR\': korean,\n                    \'FR\': french, \'SP\': spanish, \'ES\': spanish}',
        '''_STATIC_LANGUAGE_MODULES = {
    "ZH": chinese,
    "EN": english,
    "ZH_MIX_EN": chinese_mix,
    "KR": korean,
    "FR": french,
    "SP": spanish,
    "ES": spanish,
}


def _language_module(language):
    if language == "JP":
        from . import japanese

        return japanese
    return _STATIC_LANGUAGE_MODULES[language]''',
    ).replace(
        "language_module = language_module_map[language]",
        "language_module = _language_module(language)",
    )
    open(path, "w", encoding="utf-8").write(patched)
    print(f"Patched lazy Japanese import in {path}")


def patch_japanese_lazy_mecab():
    spec = importlib.util.find_spec("melo.text.japanese")
    if spec is None or spec.origin is None:
        raise SystemExit("melo.text.japanese not found")
    path = spec.origin
    text = open(path, encoding="utf-8").read()
    if "def _mecab_tagger():" in text:
        print("MeloTTS japanese already patched for lazy MeCab")
        return
    text = re.sub(
        r"try:\n    import MeCab\nexcept ImportError as e:\n    raise ImportError\(\"Japanese requires mecab-python3 and unidic-lite\.\"\) from e",
        "MeCab = None\n\n\ndef _mecab_tagger():\n    global MeCab\n    try:\n        import MeCab as _MeCab\n    except ImportError as e:\n        raise ImportError(\"Japanese requires mecab-python3 and unidic-lite.\") from e\n    MeCab = _MeCab\n    return MeCab.Tagger()",
        text,
        count=1,
    )
    text = text.replace("_TAGGER = MeCab.Tagger()", "_TAGGER = None")
    text = text.replace(
        "def text2kata(text: str) -> str:\n    parsed = _TAGGER.parse(text)",
        "def text2kata(text: str) -> str:\n    global _TAGGER\n    if _TAGGER is None:\n        _TAGGER = _mecab_tagger()\n    parsed = _TAGGER.parse(text)",
    )
    open(path, "w", encoding="utf-8").write(text)
    print(f"Patched lazy MeCab in {path}")


patch_cleaner()
patch_japanese_lazy_mecab()
PY
}

install_openvoice_unidic() {
  local venv="${VENVS}/openvoice"
  log "Installing MeCab + UniDic (official polm/unidic-py, forced download ~526 MB)..."
  "${venv}/bin/pip" install -q "mecab-python3==1.0.9" "unidic-lite==1.0.8" "unidic==1.1.0"
  "${venv}/bin/python" -m unidic download
  log "UniDic download complete."
}

install_openvoice() {
  log "=== Installing OpenVoice V2 (official: github.com/myshell-ai/OpenVoice) ==="
  local venv="${VENVS}/openvoice"
  rm -rf "${venv}"
  mkdir -p "${SRC}" "${MODELS_ROOT}/openvoice"
  if [[ ! -d "${SRC}/OpenVoice/.git" ]]; then
    git clone --depth 1 https://github.com/myshell-ai/OpenVoice.git "${SRC}/OpenVoice"
  fi
  python3 -m venv "${venv}"
  # shellcheck disable=SC1091
  source "${venv}/bin/activate"
  export PIP_NO_CACHE_DIR=1
  export PIP_ONLY_BINARY=av
  pip install -q --upgrade pip wheel
  # av wheel first — faster-whisper 0.9.x pins av==10.* (no py3.11 wheel); install av 14 then faster-whisper --no-deps
  pip install -q --only-binary=av "av==14.1.0" || pip install -q --only-binary=av "av==13.1.0"
  pip install -q huggingface_hub
  local idx
  idx="$(torch_index)"
  pip install -q torch torchaudio --index-url "${idx}"
  pip install -q "numpy>=1.24.4,<2" scipy "librosa==0.9.1" "pydub==0.25.1" "wavmark==0.0.3" \
    "eng_to_ipa==0.0.2" "inflect==7.0.0" "unidecode==1.3.7" \
    "whisper-timestamped==1.14.2" openai python-dotenv "pypinyin==0.50.0" \
    "cn2an==0.5.22" "jieba==0.42.1" "langid==1.1.6"
  pip install -q --no-deps "faster-whisper==0.9.0"
  pip install -q "ctranslate2>=3.17,<4" tokenizers onnxruntime
  pip install -q --no-deps -e "${SRC}/OpenVoice"
  pip install -q --no-deps "git+https://github.com/myshell-ai/MeloTTS.git"
  pip install -q cn2an inflect pypinyin eng-to-ipa gruut langid jieba loguru anyascii \
    cached_path fugashi g2p_en g2pkk jamo pykakasi "transformers==4.27.4" txtsplit num2words
  patch_melotts_lazy_japanese
  "${venv}/bin/python" - <<'PY'
import nltk
for pkg in ("averaged_perceptron_tagger", "averaged_perceptron_tagger_eng", "cmudict"):
    try:
        nltk.download(pkg, quiet=True)
    except Exception as exc:
        print(f"nltk {pkg}: {exc}")
PY
  install_openvoice_unidic
  patch_openvoice_cpu
  local ckpt="${MODELS_ROOT}/openvoice/checkpoints_v2"
  if [[ ! -f "${ckpt}/converter/checkpoint.pth" ]]; then
    log "Downloading OpenVoice V2 weights (official HF: myshell-ai/OpenVoiceV2)..."
    "${venv}/bin/huggingface-cli" download myshell-ai/OpenVoiceV2 \
      --local-dir "${ckpt}" \
      --local-dir-use-symlinks False \
      --exclude ".DS_Store" ".gitattributes" "README.md"
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
  local installer
  installer="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/install-latentsync.sh"
  if [[ ! -x "${installer}" && ! -f "${installer}" ]]; then
    installer="/opt/lumen/install-latentsync.sh"
  fi
  if [[ ! -f "${installer}" ]]; then
    die "install-latentsync.sh not found"
  fi
  bash "${installer}"
}

ENGINE="all"
while [[ $# -gt 0 ]]; do
  case "$1" in
    --rollback) rollback ;;
    --engine) ENGINE="${2:-all}"; shift 2 ;;
    --engine=*) ENGINE="${1#*=}"; shift ;;
    --lang-pack)
      if [[ -n "${LANG_PACKS}" ]]; then
        LANG_PACKS="${LANG_PACKS},${2:-}"
      else
        LANG_PACKS="${2:-}"
      fi
      shift 2
      ;;
    --lang-pack=*) LANG_PACKS="${1#*=}"; shift ;;
    *) shift ;;
  esac
done
# Normalize lang pack list: lowercase, no spaces
LANG_PACKS="$(echo "${LANG_PACKS}" | tr '[:upper:]' '[:lower:]' | tr -d ' ')"

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
