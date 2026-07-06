# Intelligent Engine Provisioning

Provision only engines compatible with the **cached hardware capability report**.

## Rules

1. Run `GET /api/runtime/hardware` once to cache the hardware report.
2. Provisioning **never re-detects** hardware — it reads `storage/runtime/hardware-report.json`.
3. Hardware-incompatible engines are **never installed** (remain `BLOCKED` with documented reason).
4. Compatible **alternatives** are installed automatically (e.g. Wav2Lip instead of LatentSync).
5. Engines become `READY` only after a real `POST /api/runtime/engines/{id}/test` passes.

## Commands

```bash
# Full intelligent workflow
make provision-compatible

# API
curl http://localhost:8000/api/runtime/hardware
curl http://localhost:8000/api/runtime/provision/plan
curl -X POST http://localhost:8000/api/runtime/provision/compatible
```

## UI

`/settings/runtime` → **Provision All Compatible Engines**

## Low-end local example (AMD iGPU, no CUDA)

| Engine | Action |
|---|---|
| Faster Whisper | Install |
| Ollama models | Install |
| F5-TTS, OpenVoice | Install |
| Wav2Lip | Install (lip sync fallback) |
| FFmpeg, FFmpeg AV1 | Verify |
| LatentSync, EchoMimic | **Skip** — BLOCKED |
| FFmpeg NVENC | **Skip** — BLOCKED |

## Architecture

- Engines: isolated Python venvs under `models/venvs/`
- Weights: `models/<engine>/`
- User data: `storage/`
- No model weights baked into Docker images

## Report

After provisioning: `docs/reports/Engine-Provisioning-Final.md`
