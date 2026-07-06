# Engine Compatibility (Operations)

Operational view of engine vs hardware compatibility. Authoritative matrix lives in `docs/architecture/ENGINE_COMPATIBILITY.md`.

## Cached report

The hardware report is stored at:

```
storage/runtime/hardware-report.json
```

Intelligent provisioning reads this file only — it does not re-run GPU/RAM detection.

## Provider matrix

| Engine | HOST (current PC) | REMOTE |
|---|---|---|
| Faster Whisper | Yes | Yes |
| Ollama (Gemma/Qwen/DeepSeek) | Yes | Yes |
| F5-TTS | Yes (CPU) | Yes |
| OpenVoice V2 | Yes (CPU) | Yes |
| Wav2Lip | Yes (CPU, slow) | Yes |
| LatentSync | **No** | Yes |
| EchoMimic V2 | **No** | Yes |
| FFmpeg / AV1 | Yes | Yes |
| FFmpeg NVENC | **No** | Yes |

## Lip sync policy

On `low_end_local` hardware:

- Default catalog engine: LatentSync → **BLOCKED**
- Automatic alternative: **Wav2Lip**
- Only one lip-sync engine is provisioned per run

## Manual provision guard

`POST /api/runtime/engines/latentsync/provision` returns `BLOCKED` without install attempt when the cached report shows NVIDIA/CUDA is missing.
