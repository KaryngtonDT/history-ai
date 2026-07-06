# Engine Ranking — Lumen Runtime Registry

**Date:** 2026-07-06  
**Scale:** ★ (1) poor → ★★★★★ (5) excellent  
**Reference machine:** `LOW_END_LOCAL` (AMD iGPU, 16 GB RAM, no CUDA)

Ratings reflect **fit for Lumen's localization pipeline**, not raw benchmark leaderboard position alone.

---

## Speech-to-Text

### faster_whisper_large_v3

| Dimension | Rating | Notes |
|---|---|---|
| Quality | ★★★★☆ | large-v3 WER competitive; FR/DE strong |
| Performance | ★★★☆☆ | CPU int8 ~real-time on short clips |
| Memory | ★★★★☆ | ~4 GB RAM minimum |
| Install ease | ★★★★☆ | HF prefetch; auto-provision |
| Maintenance | ★★★★★ | Stable CTranslate2 stack |
| AMD compat | ★★★★★ | No CUDA required |
| CPU compat | ★★★★☆ | Primary design path |
| Docker compat | ★★★★★ | In backend image |
| Project quality | ★★★★★ | Systran/faster-whisper |
| GitHub activity | ★★★★☆ | Active maintenance |
| License | MIT | Permissive |
| Documentation | ★★★★☆ | Good upstream + Lumen ops docs |
| **Your machine** | **Compatible** | Operational default |

### parakeet (NVIDIA)

| Dimension | Rating |
|---|---|
| Quality | ★★★★★ |
| Performance (GPU) | ★★★★★ |
| AMD / CPU | ☆☆☆☆☆ |
| Lumen integration | ★★☆☆☆ |
| **Your machine** | **Incompatible** — NVIDIA CUDA, NeMo NGC |

### canary (NVIDIA)

| Dimension | Rating |
|---|---|
| Quality | ★★★★★ |
| Multilingual | ★★★★★ |
| AMD / CPU | ☆☆☆☆☆ |
| **Your machine** | **Incompatible** — NVIDIA CUDA, NeMo NGC |

---

## Translation

### ollama_gemma3

| Dimension | Rating |
|---|---|
| Quality (localization) | ★★★★☆ |
| Performance | ★★★★☆ (4b Q4) |
| Memory | ★★★★★ |
| Install ease | ★★★★★ |
| Maintenance | ★★★★★ |
| AMD / CPU | ★★★★★ |
| Docker | ★★★★★ (Ollama sidecar) |
| License | Gemma license | Google |
| **Your machine** | **Compatible** |

### ollama_qwen3

| Dimension | Rating |
|---|---|
| Quality | ★★★★☆ |
| Style diversity | ★★★★★ |
| **Your machine** | **Compatible** |

### ollama_deepseek_r1_distill

| Dimension | Rating |
|---|---|
| Reasoning | ★★★★☆ |
| Localization speed | ★★★☆☆ |
| **Your machine** | **Compatible** (6 GB RAM min) |

---

## Text-to-Speech

### f5_tts

| Dimension | Rating |
|---|---|
| Quality | ★★★★☆ |
| Performance (CPU) | ★★★☆☆ |
| Memory | ★★★☆☆ (6 GB+) |
| Install ease | ★★★★☆ |
| Maintenance | ★★★★☆ |
| AMD compat | ★★★★☆ |
| CPU compat | ★★★★☆ |
| Project quality | ★★★★☆ |
| GitHub activity | ★★★★☆ |
| License | MIT |
| **Your machine** | **Compatible** |

### kokoro

| Dimension | Rating |
|---|---|
| Quality | ★★★★☆ |
| CPU speed | ★★★★★ |
| Install (Lumen) | ★★☆☆☆ |
| **Your machine** | **Compatible** — not packaged |

### dia

| Dimension | Rating |
|---|---|
| Quality | ★★★★★ |
| CPU fallback | ★★☆☆☆ |
| **Your machine** | **Partially incompatible** — 8 GB RAM, no CPU path in matrix |

---

## Voice Clone

### openvoice_v2

| Dimension | Rating |
|---|---|
| Quality | ★★★★☆ |
| Performance (CPU) | ★★★☆☆ |
| Memory | ★★★☆☆ |
| Install ease | ★★★☆☆ (heavy deps) |
| Maintenance | ★★★★☆ |
| AMD compat | ★★★★☆ |
| License | MIT (check MeloTTS chain) |
| **Your machine** | **Compatible** |

### chatterbox

| Dimension | Rating |
|---|---|
| Quality | ★★★★☆ |
| Lumen integration | ★☆☆☆☆ |
| **Your machine** | **Compatible*** — manual only |

### xtts_v2

| Dimension | Rating |
|---|---|
| Quality | ★★★★★ |
| Lumen integration | ★☆☆☆☆ |
| License | CPML — review required |
| **Your machine** | **Compatible*** — manual only |

---

## Lip Sync

### latentsync

| Dimension | Rating |
|---|---|
| Quality | ★★★★★ |
| VRAM | ★☆☆☆☆ (18 GB) |
| CPU | ☆☆☆☆☆ |
| Lumen integration | ★★★★☆ |
| GitHub activity | ★★★★☆ |
| License | Check upstream |
| **Your machine** | **Incompatible** — BLOCKED |
| **Future (RTX 4090+)** | READY |

### echomimic_v2

| Dimension | Rating |
|---|---|
| Quality | ★★★★☆ |
| VRAM | ★★☆☆☆ (12 GB) |
| **Your machine** | **Incompatible** — BLOCKED |

### wav2lip

| Dimension | Rating |
|---|---|
| Quality | ★★★☆☆ |
| CPU speed | ★★★☆☆ |
| Install ease | ★★★★☆ |
| Maturity | ★★★★★ (2019–2026 baseline) |
| **Your machine** | **Compatible** — recommended local |

---

## Video Render

### ffmpeg

| Dimension | Rating |
|---|---|
| Quality | ★★★★☆ (codec-dependent) |
| Performance | ★★★★☆ |
| Install | ★★★★★ |
| **Your machine** | **Compatible** — READY |

### ffmpeg_nvenc

| Dimension | Rating |
|---|---|
| Performance (encode) | ★★★★★ |
| **Your machine** | **Incompatible** — NVENC requires NVIDIA |

### ffmpeg_av1

| Dimension | Rating |
|---|---|
| Quality | ★★★★★ |
| CPU speed | ★★☆☆☆ |
| **Your machine** | **Compatible** — slow but viable |

---

## Summary ranking by capability (your machine)

| Capability | Best local today | Best future (NVIDIA) | Best remote |
|---|---|---|---|
| STT | Faster Whisper | Canary / Parakeet | Whisper API / Deepgram |
| Translation | Gemma 3 4b | Qwen 3 32b+ | Claude / Gemini |
| TTS | F5-TTS | F5-TTS (GPU) | ElevenLabs API |
| Voice | OpenVoice V2 | OpenVoice (GPU) | Remote clone API |
| Lip Sync | Wav2Lip | LatentSync | Sync Labs / GPU cloud |
| Render | FFmpeg + AV1 CPU | FFmpeg NVENC | Cloud transcode |
