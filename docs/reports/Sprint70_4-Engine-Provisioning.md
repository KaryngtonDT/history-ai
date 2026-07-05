# Sprint 70.4 — Engine Provisioning Report

**Date:** 2026-07-05  
**Mode:** PROVISIONING (post-audit)  
**Last update:** Ollama translation alternatives provisioned

## GPU engines install (2026-07-05)

Plan: `docs/operations/ENGINE_INSTALL_F5_OPENVOICE_LATENTSYNC.md`  
Installer: `make install-gpu-engines`

| Engine | Venv | Models | Runtime status | Notes |
|---|---|---|---|---|
| F5-TTS | `/models/venvs/f5-tts` | `/models/f5` | **READY** (real) | Smoke test may take minutes on CPU (first HF load) |
| OpenVoice V2 | `/models/venvs/openvoice` | pending | **BLOCKED** | Re-run after script fix (skip 526MB UniDic; PyAV/av==14.1.0) |
| LatentSync | partial | partial | **BLOCKED** | Install OOM (exit 137); needs GPU + more RAM |

### Fixes applied during install

- CLI wrappers: venv dispatch + CRLF fix on Windows (`sed -i 's/\r$//'`)
- Shim detection: bash wrappers no longer false-positive; placeholders under `/opt/lumen/placeholders/`
- F5 runner uses `/models/venvs/f5-tts/bin/f5-tts_infer-cli`

### Re-run commands

```powershell
docker compose -f docker-compose.prod-like.yml exec backend bash /opt/lumen/install-gpu-engines.sh --engine openvoice
docker compose -f docker-compose.prod-like.yml exec backend bash /opt/lumen/install-gpu-engines.sh --engine latentsync
curl -X POST http://localhost:8000/api/runtime/engines/f5_tts/test
```

## Summary

| Metric | Value |
|---|---|
| Auto-provision supported | **7** engines |
| Manual / blocked | **11** engines |
| **READY (real)** after Ollama pulls | **7 / 18** |
| Target terminal states | **READY** or **BLOCKED** only |

## Ollama translation provisioning (2026-07-05)

### Tags chosen (local / limited machine)

| Engine | Ollama tag | Size | Registry ID | Prefix match |
|---|---|---|---|---|
| Gemma 3 (default) | `gemma3:4b` | 3.3 GB | `ollama_gemma3` | `gemma3` |
| Qwen 3 (alt 1) | `qwen3:4b` | 2.5 GB | `ollama_qwen3` | `qwen3` |
| DeepSeek R1 Distill (alt 2) | `deepseek-r1:1.5b` | 1.1 GB | `ollama_deepseek_r1_distill` | `deepseek-r1` |

Larger tags documented in `docs/operations/ENGINE_MODELS.md` (`gemma3:12b`, `qwen3:8b`, `deepseek-r1:7b`, etc.).

### `ollama list` (verified)

```
deepseek-r1:1.5b    1.1 GB
qwen3:4b            2.5 GB
gemma3:4b           3.3 GB
```

### Runtime tests (`POST /api/runtime/engines/{id}/test`)

| Engine | Result | Mode | Model found |
|---|---|---|---|
| `ollama_gemma3` | **pass** | real | yes |
| `ollama_qwen3` | **pass** | real | yes |
| `ollama_deepseek_r1_distill` | **pass** | real | yes |

### Readiness (`GET /api/runtime/readiness`)

- **status:** `degraded` (expected — TTS/Voice/LipSync still blocked)
- **readyCount:** `7/18`
- Translation capability: **3/3 READY** (all Ollama engines)

## Provisioning infrastructure delivered

- `scripts/provision-engines.sh` — pulls Ollama models, prefetches Faster Whisper, calls `POST /api/runtime/provision`
- `make provision-engines` / `make setup-engines` (alias)
- Backend `EngineProvisioner` + `POST /api/runtime/provision` + `POST /api/runtime/engines/{id}/provision`
- `EngineStatusFinalizer` — non-ready engines reported as **blocked** with install command + docs link
- UI `/settings/runtime` — **Install All (Auto)**, per-engine **Install**, **Test**, **Verify Pipeline**, **Benchmark All**
- Docs: `ENGINE_INSTALLATION.md`, `ENGINE_MODELS.md`, `ENGINE_REQUIREMENTS.md`, `ENGINE_UPDATE.md`, `ENGINE_TROUBLESHOOTING.md`
- Prompt archived: `.ai/prompts/sprint-70.4-engine-provisioning.md`

## Engine matrix (current)

| Capability | Engine | Installed | Model | Configured | Executable | Runtime Ready | Smoke | Status | Notes |
|---|---|---|---|---|---|---|---|---|---|
| STT | Faster Whisper Large V3 | yes | yes | yes | yes | yes | pass | **READY** | HF large-v3 prefetched |
| STT | Parakeet | no | no | no | no | no | fail | BLOCKED | NeMo + NGC manual install |
| STT | Canary | no | no | no | no | no | fail | BLOCKED | NeMo + NGC manual install |
| Translation | Ollama + Gemma 3 | yes | `gemma3:4b` | yes | yes | yes | pass | **READY** | Default `OLLAMA_MODEL` |
| Translation | Ollama + Qwen 3 | yes | `qwen3:4b` | no | yes | yes | pass | **READY** | Alt 1 |
| Translation | DeepSeek R1 Distill | yes | `deepseek-r1:1.5b` | no | yes | yes | pass | **READY** | Alt 2, lightest |
| TTS | F5-TTS | shim | no | yes | yes | no | fail | BLOCKED | Mount real weights + replace shim |
| TTS | Kokoro | no | no | no | no | no | fail | BLOCKED | Host install |
| TTS | Dia | no | no | no | no | no | fail | BLOCKED | Host install |
| Voice | OpenVoice V2 | shim | no | yes | yes | no | fail | BLOCKED | Mount HF weights |
| Voice | Chatterbox | no | no | no | no | no | fail | BLOCKED | Manual GPU install |
| Voice | XTTS-v2 | no | no | no | no | no | fail | BLOCKED | coqui-tts manual |
| Lip sync | LatentSync | shim | no | yes | yes | no | fail | BLOCKED | Clone upstream + weights |
| Lip sync | EchoMimic V2 | no | no | no | no | no | fail | BLOCKED | Manual |
| Lip sync | MuseTalk | no | no | no | no | no | fail | BLOCKED | Manual |
| Render | FFmpeg | yes | n/a | yes | yes | yes | pass | **READY** | Bundled in image |
| Render | FFmpeg NVENC | yes | n/a | no | yes | yes | pass | **READY** | Encoder probe |
| Render | FFmpeg AV1 | yes | n/a | no | yes | yes | pass | **READY** | Encoder probe |

## Pull commands (Windows / Docker)

```powershell
docker compose -f docker-compose.prod-like.yml exec ollama ollama pull gemma3:4b
docker compose -f docker-compose.prod-like.yml exec ollama ollama pull qwen3:4b
docker compose -f docker-compose.prod-like.yml exec ollama ollama pull deepseek-r1:1.5b
```

**Note:** First `qwen3:4b` attempt failed with digest mismatch; retry succeeded.

## Expected pipeline after Ollama provisioning

| Stage | Status |
|---|---|
| STT | **READY** |
| Translation | **READY** (3 engines) |
| TTS | BLOCKED (shim) |
| Voice clone | BLOCKED (shim) |
| Lip sync | BLOCKED (shim) |
| Render | **READY** |

**3/6 configured REAL stages** without manual host installs. Full 6/6 REAL requires mounting F5, OpenVoice, LatentSync weights.

## Next prompt (recommended)

Cross-engine smoke test on a shared 30s video clip — compare STT/translation/TTS outputs across engines that reach READY.
