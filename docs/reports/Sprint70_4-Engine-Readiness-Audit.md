# Sprint 70.4 — Engine Readiness Audit

**Date:** 2026-07-05  
**Environment:** `docker-compose.prod-like.yml` (backend rebuilt, all services healthy)  
**Principle:** No engine is reported `ready` unless it is executable in **REAL** mode with satisfied requirements. Shims are **`mock` / `shim`**.

## Executive summary

| Metric | Value |
|---|---|
| Registry engines | **18** (6 capabilities × 3 each) |
| Globally ready (`status=ready`) | **4** (Faster Whisper Large V3, FFmpeg, FFmpeg NVENC, FFmpeg AV1) |
| Configured defaults ready (REAL) | **2 / 6** (STT + Render only) |
| Pipeline validation | **FAIL** (translation misconfigured, TTS/Voice/LipSync shims) |
| Silent fallbacks | **None** for configured defaults (`STT_PROVIDER=faster_whisper`, not `deterministic`) |

### Configured pipeline (what Lumen will actually use)

| Stage | Requested | Status | Mode | Notes |
|---|---|---|---|---|
| STT | `faster_whisper_large_v3` | ready | real | `STT_FASTER_WHISPER_MODEL=large-v3`, CLI + faster-whisper pip package |
| Translation | `ollama_gemma3` | misconfigured | real | Ollama API reachable; **`gemma3:4b` not pulled** |
| TTS | `f5_tts` | mock | shim | Docker shim — empty `/models/f5` |
| Voice clone | `openvoice_v2` | mock | shim | Docker shim — empty `/models/openvoice` |
| Lip sync | `latentsync` | mock | shim | Docker shim — empty `/models/latentsync` |
| Render | `ffmpeg` | ready | real | `ffmpeg 5.1.9` in container |

## Full engine matrix

| Capability | Engine | Default/Alt | Installed | Configured | Executable | Model Found | Runtime Status | Mode | Test Result | Notes |
|---|---|---|---|---|---|---|---|---|---|---|
| Speech-to-Text | Faster Whisper Large V3 | Default | yes | yes | yes | yes (config) | **ready** | real | **PASS** | HF cache on first run; model env must be `large-v3` |
| Speech-to-Text | NVIDIA Parakeet | Alt 1 | no | no | no | no | **missing** | real | FAIL | Binary `parakeet` not on PATH |
| Speech-to-Text | NVIDIA Canary | Alt 2 | no | no | no | no | **missing** | real | FAIL | Binary `canary` not on PATH |
| Translation | Ollama + Gemma 3 | Default | partial | yes | yes (API) | no | **misconfigured** | real | FAIL | Run `ollama pull gemma3:4b` |
| Translation | Ollama + Qwen 3 | Alt 1 | partial | no | yes (API) | no | misconfigured | real | FAIL | Run `ollama pull qwen3:4b` |
| Translation | Ollama + DeepSeek R1 Distill | Alt 2 | partial | no | yes (API) | no | misconfigured | real | FAIL | Run `ollama pull deepseek-r1:1.5b` |
| Text-to-Speech | F5-TTS | Default | shim | yes | yes | no | **mock** | shim | FAIL | Placeholder WAV only |
| Text-to-Speech | Kokoro TTS | Alt 1 | no | no | no | no | missing | real | FAIL | Not installed |
| Text-to-Speech | Dia | Alt 2 | no | no | no | no | missing | real | FAIL | Not installed |
| Voice Clone | OpenVoice V2 | Default | shim | yes | yes | no | **mock** | shim | FAIL | Placeholder WAV only |
| Voice Clone | Chatterbox | Alt 1 | no | no | no | no | missing | real | FAIL | Not installed |
| Voice Clone | XTTS-v2 | Alt 2 | no | no | no | no | missing | real | FAIL | Not installed |
| Lip Sync | LatentSync | Default | shim | yes | yes | no | **mock** | shim | FAIL | Shim CLI only |
| Lip Sync | EchoMimic V2 | Alt 1 | no | no | no | no | missing | real | FAIL | Not installed |
| Lip Sync | MuseTalk | Alt 2 | no | no | no | no | missing | real | FAIL | Not installed |
| Video Render | FFmpeg | Default | yes | yes | yes | n/a | **ready** | real | **PASS** | `ffmpeg -version` OK |
| Video Render | FFmpeg NVENC | Alt 1 | yes | no | yes | n/a | ready | real | PASS | NVENC encoder present in build |
| Video Render | FFmpeg AV1 | Alt 2 | yes | no | yes | n/a | ready | real | PASS | libaom-av1 encoder present |

## Verification performed

### Registry & config
- [x] 18 engines declared in `EngineCatalogDefinitions` with `id`, capability, role (default/alt), requirements
- [x] Defaults aligned: `STT_FASTER_WHISPER_MODEL=large-v3`, `OLLAMA_MODEL=gemma3:4b` in compose + `.env.example`
- [x] Empty model directories no longer satisfy readiness (`ModelScanner::hasUsableContent`)
- [x] Shim CLIs detected → `status=mock`, `mode=shim`

### Runtime API (2026-07-05)
- `GET /api/runtime/readiness` → `degraded`, 4/18 ready
- `POST /api/runtime/pipeline/validate` → **fail** with explicit reasons per stage
- `POST /api/runtime/engines/{id}/test` on configured defaults — see matrix

### Executables (backend container)
```
/usr/bin/ffmpeg
/usr/local/bin/faster-whisper   # REAL (Python + faster-whisper)
/usr/local/bin/f5-tts           # SHIM
/usr/local/bin/openvoice        # SHIM
/usr/local/bin/latentsync       # SHIM
parakeet, canary, kokoro, dia, chatterbox, xtts, echomimic, musetalk → MISSING
```

### Models volume (`/models`)
```
/models/f5         → empty
/models/openvoice  → empty
/models/latentsync → empty
/models/whisper    → empty (Faster Whisper uses HF cache + env model name)
```

### Ollama
```
GET http://ollama:11434/api/tags → {"models":[]}
```
No Gemma 3 / Qwen 3 / DeepSeek models pulled.

### Worker
- Worker **does not** read `STT_PROVIDER` directly; video pipeline STT/TTS/translation runs in **Symfony backend** via Messenger handlers and provider factories.
- Worker uses `SUMMARY_GENERATOR=deterministic` for summaries (separate from pipeline engines).

### UI (`/settings/runtime`)
- Shows `status`, `mode` (REAL/SHIM), requirements, error reason, Run Test button, last test result
- Mock engines visibly flagged — **not** shown as Ready

## Corrections applied in this audit

1. Expanded registry from 9 → **18 engines** with default/alternative roles
2. Honest readiness: `missing`, `misconfigured`, `mock` statuses + `executableFound` / `modelFound`
3. Model directory checks require **non-empty** content
4. Shim detection for F5-TTS, OpenVoice, LatentSync
5. Engine probe tests via `POST /api/runtime/engines/{id}/test`
6. Pipeline validation reports mode + reason (no silent fallback)
7. Docs: `docs/operations/ENGINE_INSTALLATION.md`
8. Makefile: `runtime-validate`, `runtime-benchmark`

## Required actions to reach full REAL pipeline

1. **Translation:** `docker compose exec ollama ollama pull gemma3:4b` (and alt models if needed)
2. **TTS / Voice / LipSync:** Install real engines + mount weights under `/models/{f5,openvoice,latentsync}` (replace shims)
3. **STT alternatives:** Install Parakeet / Canary binaries on host or extend Docker image
4. Re-run `make runtime-validate` and confirm `/settings/runtime` shows configured defaults as **ready + real**

## Success criteria status

| Criterion | Status |
|---|---|
| UI shows Ready / Missing / Misconfigured / Mock honestly | ✅ |
| Per-engine test button + result | ✅ |
| No Ready for non-executable engines | ✅ |
| No silent fallback on configured defaults | ✅ |
| All 18 engines physically installed | ❌ (expected — host install required) |
| Full REAL pipeline (6/6) | ❌ (2/6 today) |
