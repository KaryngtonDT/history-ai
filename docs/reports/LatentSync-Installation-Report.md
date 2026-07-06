# LatentSync — Installation Report

**Date:** 2026-07-06  
**Environment:** Docker prod-like (`docker-compose.prod-like.yml`) on Windows 10  
**Policy:** Strict install — no shim, no CPU fallback, READY only after real inference

---

## Verdict

| Field | Value |
|---|---|
| **Status** | **BLOCKED** |
| **Runtime readiness** | `latentsync` → **blocked** (models missing / no GPU) |
| **Install marker** | absent (`/models/.installed-latentsync` not created) |
| **Smoke inference** | **not executed** |

---

## Official source

| Item | Value |
|---|---|
| Repository | https://github.com/bytedance/LatentSync |
| Hugging Face | https://huggingface.co/ByteDance/LatentSync-1.6 |
| Version target | LatentSync **1.6** (`stage2_512.yaml`, 512×512) |
| Licence | Apache-2.0 (upstream) |
| Commit at probe | clone present under `/models/src/LatentSync` from prior attempt |

---

## GPU preflight (failed)

| Check | Result |
|---|---|
| `nvidia-smi` on **Windows host** | **not found** |
| `nvidia-smi` in **backend container** | **not found** (exit 127) |
| `torch.cuda.is_available()` | not tested — install aborted at GPU gate |
| Compose GPU config | **none** in `docker-compose.prod-like.yml` |

**Conclusion:** No CUDA-capable GPU is exposed to Lumen. Per strict policy, installation was **not continued**.

---

## exit 137 analysis (prior attempt)

| Question | Answer |
|---|---|
| **What is exit 137?** | Process received SIGKILL (128+9). In Docker/Linux this is almost always the **OOM killer**. |
| **VRAM?** | **No** — failure occurred during `pip install torch`, before any LatentSync inference or GPU workload. |
| **RAM?** | **Yes (most likely)** — installing PyTorch + torchvision + full `requirements.txt` in one step spikes memory. |
| **Docker Desktop limit?** | cgroup `memory.max` reported unlimited; failure likely **host RAM pressure** or WSL2 memory ceiling. |
| **WSL2?** | User on Windows; if Docker uses WSL2 backend, default memory cap (often 50% host RAM) can trigger 137 during large pip installs. |
| **CPU torch fallback?** | Old installer logged `WARN: No GPU` then installed **CPU** PyTorch — still multi-GB wheels, does not reduce OOM risk. |

**Evidence:** partial venv at `/models/venvs/latentsync` (created 2026-07-05), no checkpoints, no `.installed-latentsync` marker.

---

## Disk / memory at probe

| Resource | Value |
|---|---|
| UniDic N/A | — |
| LatentSync venv | partial (~empty bin from interrupted install) |
| Checkpoints | **missing** |
| Container RAM | `free -h` unavailable in minimal probe; install requires ≥ 8 GB available |

---

## Installer delivered (not run to completion)

New strict script: `scripts/install-latentsync.sh`

- Aborts without GPU (exit 2)
- Staged `torch==2.5.1+cu121` then `requirements.txt`
- Downloads only `ByteDance/LatentSync-1.6`
- Real smoke via `latentsync` CLI
- Writes `storage/runtime/install/latentsync-report.json`

Makefile:

```bash
make provision ENGINE=latentsync
```

---

## Runtime validation (current)

```text
GET /api/runtime/readiness → latentsync: blocked
POST /api/runtime/engines/latentsync/test → not READY (expected)
```

Other pipeline engines unaffected: F5-TTS and OpenVoice V2 remain **READY**.

---

## Actions required for READY

1. **Hardware:** NVIDIA GPU with **≥ 18 GB VRAM** (LatentSync 1.6 official minimum)
2. **Driver + toolkit:** NVIDIA driver + Container Toolkit on host
3. **Docker Compose:** expose GPU to `backend` (and `worker` if lip-sync jobs run there)
4. **Memory:** allocate **≥ 16 GB** to Docker Desktop / WSL2
5. **Install:** `make provision ENGINE=latentsync`
6. **Validate:** smoke MP4 + `POST /api/runtime/engines/latentsync/test`

Until all steps succeed, LatentSync must remain **BLOCKED**.

---

## Commit policy

No `feat(runtime): install and provision LatentSync` commit — installation did not reach READY with real inference.

Documentation + strict installer committed separately.
