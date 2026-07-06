# Hardware Requirements

Operational guide for running Lumen AI engines locally.

## Minimum local profile

For CPU-first development on Docker Desktop / WSL2:

- 16 GB system RAM (8 GB free recommended during provisioning)
- AMD or Intel integrated GPU is sufficient for CPU engines
- No CUDA required for the baseline pipeline

## Capability matrix (summary)

| Capability | Low-end local | NVIDIA GPU |
|---|---|---|
| Speech (Faster Whisper) | Yes | Yes |
| Translation (Ollama) | Yes | Yes |
| TTS (F5-TTS) | Yes | Faster with GPU |
| Voice clone (OpenVoice) | Yes | Faster with GPU |
| Lip sync (Wav2Lip) | Yes, slow | Faster with CUDA |
| Lip sync (LatentSync) | **No** | 18+ GB VRAM |
| Render (FFmpeg CPU/AV1) | Yes | Yes |
| Render (NVENC) | **No** | Yes |

## When to use a remote GPU provider

Use REMOTE when:

- LatentSync or EchoMimic V2 quality is required
- NVENC speed is required at scale
- Local VRAM is below engine minimum

## Docker notes

- GPU engines require NVIDIA Container Toolkit and `nvidia-smi` inside the container
- Without `.wslconfig` memory limits, pip installs may OOM (exit 137) on 16 GB hosts
- Prefer staged installs and `make provision ENGINE=<name>` per engine
