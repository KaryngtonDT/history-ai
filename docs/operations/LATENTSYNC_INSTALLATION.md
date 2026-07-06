# LatentSync — Installation (Lumen Runtime)

Production-grade provisioning for the **official** ByteDance LatentSync lip-sync engine.

**Status on this host (2026-07-06):** **BLOCKED** — no NVIDIA GPU visible to Docker. See [LatentSync-Installation-Report.md](../reports/LatentSync-Installation-Report.md).

---

## Official sources (only)

| Artifact | URL | Notes |
|---|---|---|
| **Repository** | https://github.com/bytedance/LatentSync | Apache-2.0; no forks |
| **Weights** | https://huggingface.co/ByteDance/LatentSync-1.6 | `latentsync_unet.pt`, `whisper/tiny.pt` |
| **Paper** | https://arxiv.org/abs/2412.09262 | LatentSync 1.6 |

Lumen never downloads from Google Drive, unofficial GitHub mirrors, or repackaged ZIP archives.

---

## Architecture (Lumen)

```text
Host / Docker volume
  models/
    venvs/latentsync/     # isolated Python 3.10+ venv
    src/LatentSync/       # official git clone
    latentsync/
      checkpoints/        # HF weights
      refs/               # smoke test media
  storage/                # user data (not in image)

Docker image contains only:
  /usr/local/bin/latentsync  → venv runner when READY
  /opt/lumen/engines/latentsync_runner.py
```

Models are **not** baked into the backend image.

---

## Prerequisites

### Hardware (mandatory)

| Requirement | Official minimum | Lumen policy |
|---|---|---|
| **GPU** | NVIDIA CUDA | **Required** — install aborts without `nvidia-smi` |
| **VRAM** | 8 GB (v1.5) / **18 GB** (v1.6 @ 512²) | **≥ 18 GB** for default `stage2_512.yaml` |
| **RAM (install)** | — | **≥ 8 GB available** during `pip install` |
| **Disk** | — | ~12 GB (venv + torch cu121 + weights) |

### Software

- Docker with **NVIDIA Container Toolkit** (`--gpus all` or Compose `device_requests`)
- CUDA **12.1** compatible driver (matches upstream `cu121` wheels)
- `ffmpeg`, `git` (in Lumen backend image)
- Outbound HTTPS (PyTorch wheels, Hugging Face)

### Windows / WSL2

- Install NVIDIA driver with WSL2 GPU support
- Docker Desktop → Settings → Resources → ensure sufficient memory (**≥ 16 GB** recommended)
- `docker-compose.prod-like.yml` must expose GPU to `backend` service (not configured today)

---

## Installation

### Strict installer (recommended)

```bash
# Preflight only — exits 0 if GPU OK, exits 2 if BLOCKED
docker compose -f docker-compose.prod-like.yml exec backend bash /opt/lumen/install-latentsync.sh --preflight-only

# Full install + real smoke inference (never marks READY without success)
make provision ENGINE=latentsync
```

Or:

```bash
docker compose -f docker-compose.prod-like.yml exec backend bash /opt/lumen/install-latentsync.sh
```

### What the installer does

1. **Preflight** — `nvidia-smi`, VRAM ≥ 18 GB, RAM headroom
2. **Clone** — `git clone https://github.com/bytedance/LatentSync.git` → `models/src/LatentSync`
3. **Venv** — `models/venvs/latentsync/`
4. **Deps** — staged install: `torch==2.5.1+cu121` first, then official `requirements.txt`
5. **Weights** — `huggingface-cli download ByteDance/LatentSync-1.6`
6. **Smoke** — real `latentsync` CLI inference (no mock)
7. **Marker** — `models/.installed-latentsync` only after smoke passes
8. **Report** — `storage/runtime/install/latentsync-report.json`

### Abort behaviour

If GPU is missing, the installer **stops immediately** (exit 2). It does **not**:

- install CPU-only PyTorch
- mark `.installed-latentsync`
- mark Runtime **READY**

---

## CUDA / PyTorch pins (official)

From upstream `requirements.txt` (LatentSync 1.6):

```text
torch==2.5.1
torchvision==0.20.1
--extra-index-url https://download.pytorch.org/whl/cu121
onnxruntime-gpu==1.21.0
```

---

## Models layout

```text
models/latentsync/checkpoints/
  latentsync_unet.pt
  whisper/tiny.pt
```

Verify after download:

```bash
ls -la models/latentsync/checkpoints/latentsync_unet.pt
ls -la models/latentsync/checkpoints/whisper/tiny.pt
```

---

## Smoke test

```bash
docker compose -f docker-compose.prod-like.yml exec backend \
  latentsync \
  --video /models/latentsync/refs/demo.mp4 \
  --audio /models/latentsync/refs/demo.wav \
  --base-path /models/latentsync \
  --output /tmp/latentsync-smoke.mp4
```

Expected: JSON stdout with `duration` and `output`; MP4 file > 1 KB.

---

## Runtime integration

| Component | Engine id |
|---|---|
| Runtime Registry | `latentsync` |
| Capability | `lip_sync` |
| Binary | `/usr/local/bin/latentsync` |
| Readiness | **READY** only if venv + checkpoints + successful test |

Validation:

```bash
make doctor
make runtime-validate
curl -X POST http://localhost:8000/api/runtime/engines/latentsync/test
```

UI: `/settings/runtime` — shows Installed, Models, GPU, Health, Last Test, Status.

---

## Known issues

### exit 137 during previous install

**Cause:** Linux OOM killer (SIGKILL) during `pip install torch` inside the backend container.

| Factor | Finding (2026-07-06) |
|---|---|
| GPU | Not involved — install died before inference |
| VRAM | N/A at install time |
| RAM | `pip install torch` (+ torchvision) spikes RSS; partial venv left at `models/venvs/latentsync` |
| Docker Desktop | No cgroup memory cap (`memory.max` unlimited) — host RAM pressure likely |
| CPU torch fallback | Old installer used CPU wheels when no GPU — still ~2 GB+ download, same OOM risk |

**Fix:** staged cu121 torch install + GPU preflight; never install CPU torch for LatentSync.

### No GPU in prod-like stack

`docker-compose.prod-like.yml` has **no** `deploy.resources.reservations.devices` / `runtime: nvidia`. Backend cannot see GPU even if host has one.

---

## Rollback

```bash
docker compose -f docker-compose.prod-like.yml exec backend bash /opt/lumen/install-latentsync.sh --rollback
```

Removes venv and install marker; placeholders remain until image rebuild.

---

## Enabling GPU (actions for READY)

1. **Linux host:** install NVIDIA driver + [NVIDIA Container Toolkit](https://docs.nvidia.com/datacenter/cloud-native/container-toolkit/latest/install-guide.html)
2. **Compose:** add to `backend` service:

```yaml
deploy:
  resources:
    reservations:
      devices:
        - driver: nvidia
          count: 1
          capabilities: [gpu]
```

3. **Verify:** `docker compose exec backend nvidia-smi`
4. **Re-run:** `make provision ENGINE=latentsync`

---

## Related

- Combined GPU engines plan: `ENGINE_INSTALL_F5_OPENVOICE_LATENTSYNC.md` (§ LatentSync)
- Install report: `docs/reports/LatentSync-Installation-Report.md`
