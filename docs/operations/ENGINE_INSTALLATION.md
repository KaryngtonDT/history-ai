# Engine Installation Guide

Lumen separates **Docker services** (backend, worker, PostgreSQL, Redis, frontend) from **AI engines** (STT, translation, TTS, voice clone, lip sync, render).

Runtime truth lives in **Settings → Runtime** (`/settings/runtime`). An engine is only `ready` when Lumen can execute it in **REAL** mode with required binaries/models present. Shim binaries and empty model directories are reported as `mock` or `misconfigured`.

## Required layout

```text
models/
  whisper/        # optional local cache; Faster Whisper may use HF cache when STT_FASTER_WHISPER_MODEL is set
  parakeet/
  canary/
  f5/
  kokoro/
  dia/
  openvoice/
  chatterbox/
  xtts/
  latentsync/
  echomimic/
  musetalk/
storage/
  runtime/        # validation reports, benchmark history
```

Mount `models/` and `storage/` into backend and worker containers (see `docker-compose.prod-like.yml`).

## Default pipeline configuration

| Capability | Default engine | Env vars |
|---|---|---|
| Speech-to-Text | Faster Whisper Large V3 | `STT_PROVIDER=faster_whisper`, `STT_FASTER_WHISPER_MODEL=large-v3` |
| Translation | Ollama + Gemma 3 | `TRANSLATION_PROVIDER=ollama`, `OLLAMA_BASE_URL`, `OLLAMA_MODEL=gemma3:4b` |
| Text-to-Speech | F5-TTS | `TTS_PROVIDER=f5` |
| Voice Clone | OpenVoice V2 | `VOICE_CLONE_PROVIDER=openvoice` |
| Lip Sync | LatentSync | `LIP_SYNC_PROVIDER=latentsync` |
| Video Render | FFmpeg | `VIDEO_RENDER_PROVIDER=ffmpeg` |

## Install commands (host or backend container)

### FFmpeg (default render)

```bash
ffmpeg -version
```

NVENC / AV1 variants require encoder support:

```bash
ffmpeg -hide_banner -encoders | grep -E 'nvenc|aom'
```

### Faster Whisper Large V3

Installed in the backend Docker image via `pip install faster-whisper`. Set:

```bash
STT_FASTER_WHISPER_MODEL=large-v3
```

First run downloads weights into the Hugging Face cache.

### Ollama translation models

Start Ollama, then pull models for defaults and alternatives:

```bash
ollama pull gemma3:4b
ollama pull qwen3:4b
ollama pull deepseek-r1:1.5b
```

Set `OLLAMA_MODEL` to the active model tag. Runtime Center lists each variant separately.

### F5-TTS / OpenVoice / LatentSync (real weights)

Use the dedicated installer (venv + model download):

```bash
make install-gpu-engines
# or per engine:
docker compose -f docker-compose.prod-like.yml exec backend bash /opt/lumen/install-gpu-engines.sh --engine f5
```

See **`docs/operations/ENGINE_INSTALL_F5_OPENVOICE_LATENTSYNC.md`** for repos, CUDA, disk, rollback.

Until weights are installed, Runtime Center shows status `blocked` or `misconfigured` (not silent mock).

### Missing alternatives

Install on the host and expose binaries on `PATH`, or add CLI shims under `infrastructure/docker/backend/bin/`:

| Capability | Alternative | Binary name |
|---|---|---|
| STT | NVIDIA Parakeet | `parakeet` |
| STT | NVIDIA Canary | `canary` |
| TTS | Kokoro | `kokoro` |
| TTS | Dia | `dia` |
| Voice clone | Chatterbox | `chatterbox` |
| Voice clone | XTTS-v2 | `xtts` |
| Lip sync | EchoMimic V2 | `echomimic` |
| Lip sync | MuseTalk | `musetalk` |

## Verification

```bash
make prod-rebuild
make migrate
make doctor
make runtime-validate
make runtime-benchmark
curl http://localhost:8000/api/runtime/readiness
```

Open `/settings/runtime` and run **Run Test** on each configured engine.

## No silent fallbacks

Pipeline providers throw if misconfigured (`STT_PROVIDER=deterministic` is explicit, not silent). Mock/shim engines are visible in Runtime Center with `mode: shim` and `status: mock`.
