# F5-TTS · OpenVoice V2 · LatentSync — Installation Plan

Separate provisioning guide for the three default pipeline engines that require GPU weights and Python stacks beyond the Lumen PHP image.

**Automated installer:** `scripts/install-f5-openvoice-latentsync.sh`  
**Make target:** `make install-gpu-engines`

---

## Shared layout (Lumen)

```text
models/
  venvs/
    f5-tts/           # isolated venv
    openvoice/
    latentsync/
  src/
    F5-TTS/           # optional editable clone
    OpenVoice/
    LatentSync/
  f5/                 # F5 checkpoints + refs/
  openvoice/          # checkpoints_v2/
  latentsync/         # checkpoints/ (unet + whisper)
storage/runtime/install/
  manifest.json       # install timestamp + versions
  rollback/           # previous CLI symlinks + manifest snapshots
```

Lumen CLIs (must match backend providers):

| Binary | Args | JSON output |
|---|---|---|
| `f5-tts` | `--text --voice --model --base-path --output` | `{duration, format, output}` |
| `openvoice` | `--reference --source --text --model --base-path --output` | `{duration, sampleRate, output}` |
| `latentsync` | `--video --audio --model --base-path --output` | `{duration, output}` |

---

## 1. F5-TTS

| Item | Value |
|---|---|
| **Official repo** | https://github.com/SWivid/F5-TTS |
| **PyPI package** | `f5-tts` (inference) |
| **Model weights** | https://huggingface.co/SWivid/F5-TTS |
| **Python** | **3.10–3.11** (3.11 recommended) |
| **CUDA** | Optional for inference; **CUDA 12.4+** recommended with matching PyTorch wheel. CPU works (slow). |
| **System deps** | `ffmpeg`, `git`, `build-essential` (compile), `libsndfile1` |
| **Model path** | `models/f5/` — HF snapshot + `models/f5/refs/default.wav` (+ `.txt` transcript) |
| **Venv** | `models/venvs/f5-tts` — **yes**, isolated |
| **Disk** | ~6 GB (venv + PyTorch CPU) / ~8 GB (GPU torch) + ~1.5 GB weights |
| **Install time** | 15–45 min (network); +10 min first inference (weight load) |
| **Minimal test** | See [Verification](#verification) |
| **Rollback** | Remove `models/venvs/f5-tts`, `models/f5`, restore shim — see [Rollback](#rollback) |

### Known risks

- CUDA / PyTorch wheel mismatch → `no kernel image` errors.
- First run downloads extra HF assets (Vocos vocoder) — needs outbound network.
- Reference audio required: without `models/f5/refs/default.wav` synthesis fails.
- numpy / torch version conflicts if sharing one venv with other engines.

### Install (manual)

```bash
docker compose -f docker-compose.prod-like.yml exec backend bash /opt/lumen/install-gpu-engines.sh --engine f5
```

---

## 2. OpenVoice V2

| Item | Value |
|---|---|
| **Official repo** | https://github.com/myshell-ai/OpenVoice |
| **Model weights** | https://huggingface.co/myshell-ai/OpenVoiceV2 or S3 zip → `checkpoints_v2/` |
| **Python** | **3.10** (recommended; upstream 3.9) |
| **CUDA** | Recommended; **CPU possible** with patches to `se_extractor.py` (Whisper `device=cpu`, `compute_type=float32`) |
| **System deps** | `ffmpeg`, `git`, `libgl1`, `unidic` (Japanese, via `python -m unidic download`) |
| **Extra pip** | `git+https://github.com/myshell-ai/MeloTTS.git` |
| **Model path** | `models/openvoice/checkpoints_v2/` |
| **Venv** | `models/venvs/openvoice` — **yes**, isolated |
| **Disk** | ~5 GB (venv) + ~500 MB checkpoints |
| **Install time** | 20–60 min (MeloTTS + unidic download) |
| **Minimal test** | See [Verification](#verification) |
| **Rollback** | Remove venv + `models/openvoice`, restore shim |

### Known risks

- Default code paths assume **CUDA** for tone extraction (Whisper in `se_extractor.py`).
- MeloTTS + unidic adds significant download size; Lumen uses **Python 3.10 venv** and prebuilt `av` wheel to avoid PyAV compile failures on 3.11.
- Windows native install unsupported upstream — use Docker/WSL2/Linux only.
- Multi-speaker reference quality strongly affects clone output.

### Install (manual)

```bash
docker compose -f docker-compose.prod-like.yml exec backend bash /opt/lumen/install-gpu-engines.sh --engine openvoice
```

---

## 3. LatentSync

| Item | Value |
|---|---|
| **Official repo** | https://github.com/bytedance/LatentSync |
| **Model weights** | HuggingFace `ByteDance/LatentSync-1.6` → `latentsync_unet.pt`, `whisper/tiny.pt` |
| **Python** | **3.10.13** (upstream `setup_env.sh`) |
| **CUDA** | **Required for practical inference** (upstream targets CUDA 12.1 + PyTorch cu121). CPU not supported for production lip-sync. |
| **System deps** | `ffmpeg`, `git`, `libgl1`, OpenCV deps |
| **Model path** | `models/latentsync/checkpoints/` |
| **Venv** | `models/venvs/latentsync` — **yes**, isolated |
| **Disk** | ~8 GB (venv + torch) + ~2 GB checkpoints |
| **Install time** | 30–90 min |
| **Minimal test** | See [Verification](#verification) |
| **Rollback** | Remove venv + `models/latentsync`, restore shim |

### Known risks

- **GPU mandatory** for usable lip-sync; Docker Desktop must expose NVIDIA GPU (`--gpus all`).
- CUDA 12.1 vs host driver mismatch common on new RTX cards — may need cu124/cu128 wheels.
- Inference is memory-heavy: **8 GB VRAM minimum**, 16 GB+ recommended.
- Long videos OOM — Lumen pipeline uses short segments but test clips should stay ≤30 s.

### Install (manual)

```bash
docker compose -f docker-compose.prod-like.yml exec backend bash /opt/lumen/install-gpu-engines.sh --engine latentsync
```

---

## Verification

Run inside backend container after install:

```bash
# F5-TTS
f5-tts --text "Hello from Lumen." --voice default --model F5-TTS \
  --base-path /models/f5 --output /tmp/f5-test.wav
python3 -c "import json,wave; w=wave.open('/tmp/f5-test.wav'); print(json.dumps({'duration': w.getnframes()/w.getframerate(), 'format':'wav', 'output':'/tmp/f5-test.wav'}))"

# OpenVoice (needs reference + source wav)
openvoice --reference /models/openvoice/refs/default.wav \
  --source /models/openvoice/refs/default.wav \
  --text "Hello clone." --model openvoice_v2 \
  --base-path /models/openvoice --output /tmp/ov-test.wav

# LatentSync (needs short mp4 + wav)
latentsync --video /models/latentsync/refs/demo.mp4 \
  --audio /models/latentsync/refs/demo.wav \
  --model latentsync --base-path /models/latentsync \
  --output /tmp/ls-test.mp4
```

Runtime API:

```bash
curl -X POST http://localhost:8000/api/runtime/engines/f5_tts/test
curl -X POST http://localhost:8000/api/runtime/engines/openvoice_v2/test
curl -X POST http://localhost:8000/api/runtime/engines/latentsync/test
curl http://localhost:8000/api/runtime/readiness
```

---

## Rollback

Per engine:

```bash
# Example: F5-TTS
rm -rf /models/venvs/f5-tts /models/f5
ln -sf /opt/lumen/shims/f5-tts /usr/local/bin/f5-tts
```

Full rollback (all three):

```bash
bash /opt/lumen/install-gpu-engines.sh --rollback
```

Manifest snapshots live in `storage/runtime/install/rollback/` (on the `lumen_storage` volume when using prod-like compose).

---

## GPU vs CPU matrix (this machine / Docker)

| Engine | CPU-only Docker | GPU Docker (`nvidia-container-toolkit`) |
|---|---|---|
| F5-TTS | Works, slow (~RTF 0.5–2) | Recommended |
| OpenVoice V2 | Works with CPU patch | Recommended |
| LatentSync | **Blocked** (install completes, inference fails) | **Required** |

---

## Larger alternatives (not default)

| Engine | Larger model / variant | Notes |
|---|---|---|
| F5-TTS | `F5TTS_v1_Base` (default), BigVGAN vocoder | Editable repo install for finetuning |
| OpenVoice | V1 checkpoints (legacy) | V2 is Lumen default |
| LatentSync | 1.6 (512) vs 1.5 | 1.6 preferred for teeth/lip clarity |
