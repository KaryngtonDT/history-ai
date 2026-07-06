# Hardware Profiles

Lumen classifies each machine into a hardware profile before evaluating engine compatibility.

## Profile types

| Profile | Description |
|---|---|
| `cpu_only` | No usable GPU detected |
| `low_end_local` | Integrated or non-CUDA GPU, limited VRAM |
| `mid_range_nvidia` | NVIDIA GPU with 8–15 GB VRAM |
| `high_end_nvidia` | NVIDIA GPU with 16–39 GB VRAM |
| `enterprise_gpu` | NVIDIA GPU with 40+ GB VRAM |
| `unknown` | Detection incomplete |

## Detected capabilities

The runtime probes:

- CPU model
- RAM total and available
- GPU vendor and name
- VRAM (NVIDIA only)
- CUDA, ROCm, DirectML
- Docker GPU access and memory limits
- WSL2
- Python, FFmpeg, Ollama
- Disk free space

## Current developer machine (reference)

| Field | Value |
|---|---|
| Profile | `low_end_local` |
| GPU | AMD Radeon integrated graphics |
| CUDA | false |
| RAM | ~16 GB total, ~4–5 GB free |
| Docker GPU | false |
| WSL2 | true |

## API

- `GET /api/runtime/hardware`
- `GET /api/runtime/hardware/profile`

## Recommended pipelines

Profiles drive the recommended local pipeline shown in `/settings/runtime`:

- **Low-end local:** Faster Whisper, Gemma 3, F5-TTS, OpenVoice, **Wav2Lip**, FFmpeg AV1 (CPU)
- **High-end NVIDIA:** LatentSync and NVENC become viable
