# Runtime Technology Review — After Sprint 70.6 Dashboard

**Generated:** 2026-07-06 (Sprint 70.7)  
**Source:** Runtime Dashboard — single source of truth (`hardwareRedetected: false`)

## Executive Summary

Runtime Score **43.4**. Platform Score from live dashboard. Hardware profile **`cpu_only`**.

The completion planner identified **1** recommended compatible engine still not READY:

| Engine | Capability | Action |
| --- | --- | --- |
| `wav2lip` | Lip Sync | Provision attempted — model checkpoint install requires root + network in container |

All other recommended pipeline engines (Faster Whisper, Ollama Gemma 3, F5-TTS, OpenVoice V2, FFmpeg AV1) are READY.

## Score Evolution

| Metric | Before completion | After attempt |
| --- | ---: | ---: |
| Runtime Score | 43.4 | 42.4 |
| Platform Score | (live) | (live) |

Score dip reflects failed Wav2Lip benchmark probe until models are installed.

## Current Pipeline (`cpu_only`)

| Stage | Engine ID |
| --- | --- |
| speech | `faster_whisper_large_v3` |
| translation | `ollama_gemma3` |
| tts | `f5_tts` |
| voiceClone | `openvoice_v2` |
| lipSync | `wav2lip` |
| render | `ffmpeg_av1` |

## Future NVIDIA Pipeline

| Stage | Engine ID |
| --- | --- |
| speech | `parakeet` / `canary` |
| lipSync | `latentsync` |
| render | `ffmpeg_nvenc` |

## Lip Sync — Detail

- **Reference:** LatentSync (BLOCKED — CUDA)
- **Recommended:** Wav2Lip
- **Current:** latentsync (configured default, blocked)
- **Missing:** Wav2Lip model files (`/models/wav2lip`)
- **Alternative:** Wav2Lip (CPU)
- **Future upgrade:** RTX 4090 + CUDA → LatentSync (+8 Runtime Score estimated)

## Premium Engines (retained)

| Engine | Reason | Alternative | Est. gain |
| --- | --- | --- | ---: |
| LatentSync | NVIDIA CUDA + VRAM | Wav2Lip | +8 |
| EchoMimic V2 | NVIDIA CUDA | Wav2Lip | +6 |
| Parakeet / Canary | NVIDIA NeMo | Faster Whisper | +4 |
| FFmpeg NVENC | NVENC hardware | FFmpeg AV1 | +3 |

## Validation

```bash
make runtime-completion
make runtime-completion-execute   # after Wav2Lip models installed
make runtime-validate
make runtime-benchmark
```

See [Sprint70_7-Verification.md](./Sprint70_7-Verification.md).
