# Engine Requirements (Provisioning)

Per-engine requirements used by the intelligent provisioner.

## Auto-provisioned (when hardware-compatible)

| Engine | RAM | GPU | Notes |
|---|---|---|---|
| faster_whisper_large_v3 | 4 GB | CPU ok | HuggingFace cache |
| ollama_* | 4–6 GB | CPU ok | Ollama pull |
| f5_tts | 6 GB | CPU ok | venv + HF weights |
| openvoice_v2 | 6 GB | CPU ok | venv + HF weights |
| wav2lip | 6 GB | CPU ok | official repo + checkpoint |
| ffmpeg | — | — | bundled in image |
| ffmpeg_av1 | 4 GB | CPU ok | libaom encoder |

## Never auto-provisioned on low-end local

| Engine | Requirement | Alternative |
|---|---|---|
| latentsync | NVIDIA CUDA, 18 GB VRAM | wav2lip |
| echomimic_v2 | NVIDIA CUDA, 12 GB VRAM | wav2lip |
| ffmpeg_nvenc | NVIDIA NVENC | ffmpeg_av1 |
| parakeet, canary | NVIDIA CUDA | faster_whisper |

## Manual-only engines

Kokoro, Dia, Chatterbox, XTTS-v2 — documented in `ENGINE_INSTALLATION.md`, excluded from intelligent provisioning.

## Wav2Lip

```bash
docker compose exec backend bash /opt/lumen/install-wav2lip.sh
```

Checkpoint: `models/wav2lip/wav2lip_gan.pth`  
Source: official [Rudrabha/Wav2Lip](https://github.com/Rudrabha/Wav2Lip)
