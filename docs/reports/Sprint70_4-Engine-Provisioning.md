# Sprint 70.4 — Engine Provisioning Report

**Date:** 2026-07-05  
**Mode:** PROVISIONING (post-audit)  
**Command:** `make provision-engines` / `scripts/provision-engines.sh`

## Summary

| Metric | Value |
|---|---|
| Auto-provision supported | **7** engines |
| Manual / blocked | **11** engines |
| Target terminal states | **READY** or **BLOCKED** only |

## Provisioning infrastructure delivered

- `scripts/provision-engines.sh` — pulls Ollama models, prefetches Faster Whisper, calls `POST /api/runtime/provision`
- `make provision-engines` / `make setup-engines` (alias)
- Backend `EngineProvisioner` + `POST /api/runtime/provision` + `POST /api/runtime/engines/{id}/provision`
- `EngineStatusFinalizer` — non-ready engines reported as **blocked** with install command + docs link
- UI `/settings/runtime` — **Install All (Auto)**, per-engine **Install**, **Test**, **Verify Pipeline**, **Benchmark All**
- Docs: `ENGINE_INSTALLATION.md`, `ENGINE_MODELS.md`, `ENGINE_REQUIREMENTS.md`, `ENGINE_UPDATE.md`, `ENGINE_TROUBLESHOOTING.md`
- Prompt archived: `.ai/prompts/sprint-70.4-engine-provisioning.md`

## Engine matrix (after provisioning run)

| Capability | Engine | Installed | Model | Configured | Executable | Runtime Ready | Smoke | Status | Notes |
|---|---|---|---|---|---|---|---|---|---|
| STT | Faster Whisper Large V3 | auto | prefetch | yes | yes | **ready** | pass | READY | `make provision-engines` prefetches HF large-v3 |
| STT | Parakeet | no | no | no | no | no | fail | BLOCKED | NeMo + NGC manual install |
| STT | Canary | no | no | no | no | no | fail | BLOCKED | NeMo + NGC manual install |
| Translation | Ollama + Gemma 3 | auto | pull | yes | yes | after pull | after pull | READY/BLOCKED | `ollama pull gemma3:4b` |
| Translation | Ollama + Qwen 3 | auto | pull | no | yes | after pull | after pull | READY/BLOCKED | `ollama pull qwen3:4b` |
| Translation | DeepSeek R1 Distill | auto | pull | no | yes | after pull | after pull | READY/BLOCKED | `ollama pull deepseek-r1:1.5b` |
| TTS | F5-TTS | shim | no | yes | yes | no | fail | BLOCKED | Mount real weights + replace shim |
| TTS | Kokoro | no | no | no | no | no | fail | BLOCKED | Host install |
| TTS | Dia | no | no | no | no | no | fail | BLOCKED | Host install |
| Voice | OpenVoice V2 | shim | no | yes | yes | no | fail | BLOCKED | Mount HF weights |
| Voice | Chatterbox | no | no | no | no | no | fail | BLOCKED | Manual GPU install |
| Voice | XTTS-v2 | no | no | no | no | no | fail | BLOCKED | coqui-tts manual |
| Lip sync | LatentSync | shim | no | yes | yes | no | fail | BLOCKED | Clone upstream + weights |
| Lip sync | EchoMimic V2 | no | no | no | no | no | fail | BLOCKED | Manual |
| Lip sync | MuseTalk | no | no | no | no | no | fail | BLOCKED | Manual |
| Render | FFmpeg | yes | n/a | yes | yes | **ready** | pass | READY | Bundled in image |
| Render | FFmpeg NVENC | yes | n/a | no | yes | **ready** | pass | READY | Encoder probe |
| Render | FFmpeg AV1 | yes | n/a | no | yes | **ready** | pass | READY | Encoder probe |

## Auto-provision steps (run locally)

```powershell
# From repo root (Docker Desktop running)
docker compose -f docker-compose.prod-like.yml exec ollama ollama pull gemma3:4b
docker compose -f docker-compose.prod-like.yml exec ollama ollama pull qwen3:4b
docker compose -f docker-compose.prod-like.yml exec ollama ollama pull deepseek-r1:1.5b
docker compose -f docker-compose.prod-like.yml exec backend python3 -c "from faster_whisper import WhisperModel; WhisperModel('large-v3', device='cpu', compute_type='int8')"
curl -X POST http://localhost:8000/api/runtime/provision
make runtime-validate
```

## Expected pipeline after successful Ollama pulls

| Stage | Status |
|---|---|
| STT | READY |
| Translation | READY |
| TTS | BLOCKED (shim) |
| Voice clone | BLOCKED (shim) |
| Lip sync | BLOCKED (shim) |
| Render | READY |

**3/6 configured REAL stages** without manual host installs. Full 6/6 REAL requires mounting F5, OpenVoice, LatentSync weights.

## Next prompt (recommended)

Cross-engine smoke test on a shared 30s video clip — compare STT/translation/TTS outputs across engines that reach READY.
