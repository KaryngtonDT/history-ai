# GPU Requirements

## NVIDIA CUDA engines

These engines require an NVIDIA GPU with CUDA visible to the runtime:

| Engine | VRAM (approx.) | CPU fallback |
|---|---|---|
| LatentSync | 18 GB | No |
| EchoMimic V2 | 12 GB | No |
| Parakeet / Canary | 8 GB | No |
| FFmpeg NVENC | NVENC hardware | No |

## CPU / integrated GPU engines

| Engine | Notes |
|---|---|
| Faster Whisper | CPU ok; GPU optional |
| Ollama (Gemma/Qwen) | CPU ok |
| F5-TTS | CPU ok |
| OpenVoice V2 | CPU ok |
| Wav2Lip | CPU ok, slower |
| FFmpeg / AV1 | CPU encode, slower |

## AMD integrated graphics (reference machine)

- CUDA: **not available**
- LatentSync: **blocked** — use Wav2Lip locally or remote NVIDIA
- EchoMimic V2: **blocked** — same fallback
- NVENC: **blocked** — use FFmpeg AV1 CPU path

## Detection

```bash
curl http://localhost/api/runtime/hardware
curl http://localhost/api/runtime/engines/latentsync/blocked-reason
```
