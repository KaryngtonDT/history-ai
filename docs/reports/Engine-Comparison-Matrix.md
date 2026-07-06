# Engine Comparison Matrix

**Date:** 2026-07-06  
**Reference machine:** `LOW_END_LOCAL`

---

## Master decision matrix

| Capability | Current catalog default | Installed on your PC* | Blocked future engine | Recommended local | Recommended future (NVIDIA) | Recommended remote |
|---|---|---|---|---|---|---|
| Speech-to-Text | Faster Whisper | Faster Whisper | Parakeet, Canary | Faster Whisper | Canary | Deepgram / AssemblyAI |
| Translation | Ollama + Gemma 3 | Gemma 3 (+ alts) | — | Gemma 3 4b | Qwen 3 14b+ | Gemini / Claude API |
| Text-to-Speech | F5-TTS | F5-TTS | Dia (RAM) | F5-TTS → Kokoro** | F5-TTS GPU | ElevenLabs |
| Voice Clone | OpenVoice V2 | OpenVoice V2 | — | OpenVoice V2 | OpenVoice GPU | Resemble / API |
| Lip Sync | LatentSync | Wav2Lip (target) | LatentSync, EchoMimic | Wav2Lip | LatentSync | Remote NVIDIA worker |
| Video Render | FFmpeg | FFmpeg | FFmpeg NVENC | FFmpeg AV1 CPU | FFmpeg NVENC | Cloud transcode |

\*Based on Sprint 70.45 verification + intelligent provisioning design; actual install state may vary.  
\*\*Kokoro = integration candidate, not yet in registry as auto-provision.

---

## Speech-to-Text — comparative table

| | Faster Whisper | Parakeet | Canary |
|---|---|---|---|
| **Status (you)** | READY* | BLOCKED | BLOCKED |
| **Provider** | HOST | HOST | HOST |
| **HW compat** | Compatible | Incompatible | Incompatible |
| **Why** | CPU int8 | NVIDIA CUDA + NeMo | NVIDIA CUDA + NeMo |
| **Official default?** | Yes | Alt 1 (NVIDIA tier) | Alt 2 (NVIDIA tier) |
| **2026 still best?** | Yes (CPU tier) | Yes (NVIDIA tier) | Yes (multilingual NVIDIA) |
| **Better alt exists?** | Distil-Whisper (speed) | — | — |
| **Mature?** | Yes | Yes | Yes |
| **Docker** | Yes | Manual NeMo | Manual NeMo |
| **Lumen Runtime** | Full | Catalog only | Catalog only |
| **Quality** | ★★★★☆ | ★★★★★ | ★★★★★ |
| **CPU** | ★★★★☆ | ☆ | ☆ |

---

## Translation — comparative table

| | Gemma 3 | Qwen 3 | DeepSeek R1 Distill |
|---|---|---|---|
| **Status (you)** | READY* | READY* | READY* |
| **Provider** | DOCKER | DOCKER | DOCKER |
| **HW compat** | Compatible | Compatible | Compatible |
| **Model tag** | gemma3:4b | qwen3:4b | deepseek-r1:1.5b |
| **Official default?** | Yes | Alt 1 | Alt 2 |
| **2026 still best?** | Yes (balanced) | Yes (alt style) | Niche (reasoning) |
| **Better alt?** | Llama 3.3 8b | Mistral Small | — |
| **Mature?** | Yes | Yes | Yes |
| **RAM** | ~4 GB | ~4 GB | ~6 GB |

---

## Text-to-Speech — comparative table

| | F5-TTS | Kokoro | Dia |
|---|---|---|---|
| **Status (you)** | READY* | MISSING | BLOCKED/MISSING |
| **Provider** | HOST | HOST | HOST |
| **HW compat** | Compatible | Compatible | Partial |
| **Why blocked** | — | Not packaged | 8 GB RAM, no CPU path |
| **Official default?** | Yes | Alt 1 | Alt 2 |
| **2026 still best?** | Yes (quality) | Strong CPU challenger | Quality niche |
| **Better alt?** | CosyVoice, Fish Speech | — | — |
| **Lumen integration** | Full | Manual | Manual |
| **Quality** | ★★★★☆ | ★★★★☆ | ★★★★★ |
| **CPU speed** | ★★★☆☆ | ★★★★★ | ★★☆☆☆ |

---

## Voice Clone — comparative table

| | OpenVoice V2 | Chatterbox | XTTS-v2 |
|---|---|---|---|
| **Status (you)** | READY* | MISSING | MISSING |
| **Provider** | HOST | HOST | HOST |
| **HW compat** | Compatible | Compatible* | Compatible* |
| **Official default?** | Yes | Alt 1 | Alt 2 |
| **2026 still best?** | Yes (integrated) | Unclear | Yes (quality) |
| **Better alt?** | CosyVoice, SeedVC | — | — |
| **License** | MIT chain | Check | CPML |
| **Install** | Auto | Manual | Manual |
| **Quality** | ★★★★☆ | ★★★★☆ | ★★★★★ |

---

## Lip Sync — comparative table

| | LatentSync | EchoMimic V2 | Wav2Lip |
|---|---|---|---|
| **Status (you)** | BLOCKED | BLOCKED | MISSING* |
| **Provider** | HOST / REMOTE | HOST / REMOTE | HOST |
| **HW compat** | Incompatible | Incompatible | Compatible |
| **Why** | CUDA + 18 GB VRAM | CUDA + 12 GB VRAM | — |
| **Catalog role** | Default | Alt 1 | Alt 2 |
| **Operational (you)** | No | No | **Yes (recommended)** |
| **2026 still best?** | Yes (premium) | Alt premium | Yes (CPU baseline) |
| **Quality** | ★★★★★ | ★★★★☆ | ★★★☆☆ |
| **Future hardware** | RTX 4090/5090 | RTX 3090+ | Always available |

---

## Video Render — comparative table

| | FFmpeg | FFmpeg NVENC | FFmpeg AV1 |
|---|---|---|---|
| **Status (you)** | READY | BLOCKED | READY* |
| **Provider** | HOST | HOST | HOST |
| **HW compat** | Compatible | Incompatible | Compatible |
| **Why blocked** | — | NVENC requires NVIDIA | — |
| **Encoder** | libx264 etc. | h264_nvenc | libaom-av1 |
| **Speed (you)** | Fast | N/A | Slow |
| **Quality** | ★★★★☆ | ★★★★☆ | ★★★★★ |
| **Future** | Always | RTX any | SVT-AV1 candidate |

---

## AI Engine Registry vs Runtime Catalog

| Capability | AIEngineRegistry default | Runtime catalog default | Aligned? |
|---|---|---|---|
| STT | faster_whisper | faster_whisper_large_v3 | Yes |
| Translation | ollama | ollama_gemma3 | Yes |
| TTS | f5_tts | f5_tts | Yes |
| Voice | openvoice | openvoice_v2 | Yes |
| Lip Sync | latentsync | latentsync | Yes (hardware overrides to wav2lip) |
| Render | ffmpeg | ffmpeg | Yes |

---

## Per-engine status legend (your machine)

| Status | Engines |
|---|---|
| **READY** | faster_whisper*, f5_tts*, openvoice_v2*, ollama_*, ffmpeg, ffmpeg_av1* |
| **BLOCKED (hardware)** | latentsync, echomimic_v2, parakeet, canary, ffmpeg_nvenc, dia |
| **MISSING (install)** | wav2lip, kokoro, chatterbox, xtts_v2 |
| **MOCK** | None in Real-mode catalog |

\*Depends on provision state.
