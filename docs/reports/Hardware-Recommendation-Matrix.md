# Hardware Recommendation Matrix

**Date:** 2026-07-06  
**Source:** `HardwareReportBuilder::recommendedPipeline()` + `EngineRequirementMatrix`  
**Purpose:** Decision aid — which pipeline to run per machine profile

---

## Profile overview

| Profile | GPU | CUDA | Typical RAM | Lip sync default | Render default |
|---|---|---|---|---|---|
| `cpu_only` | None | No | 8–16 GB | Wav2Lip | FFmpeg AV1 |
| `low_end_local` | AMD/Intel iGPU | No | 16 GB | Wav2Lip | FFmpeg AV1 |
| `mid_range_nvidia` | RTX 3060–4070 | Yes | 16–32 GB | Wav2Lip* | FFmpeg NVENC |
| `high_end_nvidia` | RTX 4090 / 3090 | Yes | 32–64 GB | **LatentSync** | FFmpeg NVENC |
| `enterprise_gpu` | A100 / H100 / 5090 | Yes | 64+ GB | **LatentSync** | FFmpeg NVENC |

\*Mid-range may use Wav2Lip until VRAM ≥18 GB for LatentSync.

---

## LOW_END_LOCAL (your machine)

```
Machine Profile: LOW_END_LOCAL
AMD Radeon integrated · 16 GB RAM · no CUDA · Docker WSL2
```

### Recommended pipeline

| Stage | Engine | Engine ID | Provider | Status |
|---|---|---|---|---|
| Speech | Faster Whisper Large V3 | `faster_whisper_large_v3` | HOST | READY* |
| Translation | Ollama + Gemma 3 | `ollama_gemma3` | DOCKER | READY* |
| TTS | F5-TTS | `f5_tts` | HOST | READY* |
| Voice Clone | OpenVoice V2 | `openvoice_v2` | HOST | READY* |
| Lip Sync | **Wav2Lip** | `wav2lip` | HOST | MISSING* |
| Render | FFmpeg AV1 (CPU) | `ffmpeg_av1` | HOST | READY* |

### Blocked on this profile (keep in registry)

| Engine | Reason | Use instead |
|---|---|---|
| LatentSync | CUDA + 18 GB VRAM | Wav2Lip / REMOTE |
| EchoMimic V2 | CUDA + 12 GB VRAM | Wav2Lip |
| Parakeet, Canary | NVIDIA NeMo | Faster Whisper |
| FFmpeg NVENC | NVENC hardware | FFmpeg AV1 |
| Dia | RAM + no CPU path | F5-TTS |

### Optional alts (compatible, not default)

| Stage | Alternative |
|---|---|
| Translation | Qwen 3, DeepSeek R1 Distill |
| TTS | Kokoro (when packaged) |
| Render | FFmpeg (H.264 fast mux) |

---

## CPU_ONLY

```
Machine Profile: CPU_ONLY
No discrete GPU · no CUDA
```

### Recommended pipeline

| Stage | Engine |
|---|---|
| Speech | Faster Whisper (small/base if RAM <8 GB) |
| Translation | Ollama Gemma 3 4b |
| TTS | Kokoro ONNX (preferred) or F5-TTS |
| Voice Clone | OpenVoice V2 (slow) |
| Lip Sync | Wav2Lip |
| Render | FFmpeg AV1 or copy codec |

### Notes

- Consider `large-v3` → `medium` Whisper if RAM constrained.
- Avoid Dia, LatentSync, all NVIDIA engines.

---

## MID_RANGE_NVIDIA

```
Machine Profile: MID_RANGE_NVIDIA
RTX 3060–4070 · 8–16 GB VRAM · CUDA yes
```

### Recommended pipeline

| Stage | Engine | Notes |
|---|---|---|
| Speech | Faster Whisper (GPU ctranslate2) | Optional upgrade |
| Translation | Gemma 3 or Qwen 3 8b | GPU offload in Ollama |
| TTS | F5-TTS GPU | Faster inference |
| Voice Clone | OpenVoice V2 GPU | |
| Lip Sync | Wav2Lip GPU | Until 18 GB VRAM GPU |
| Render | **FFmpeg NVENC** | Fast H.264/HEVC |

### Blocked / deferred

| Engine | VRAM needed | When |
|---|---|---|
| LatentSync | 18 GB | RTX 4090 tier |
| EchoMimic V2 | 12 GB | May work on 12 GB cards |

---

## HIGH_END_NVIDIA

```
Machine Profile: HIGH_END_NVIDIA
RTX 4090 / 3090 24 GB · CUDA · 32+ GB RAM
```

### Recommended pipeline

| Stage | Engine |
|---|---|
| Speech | Faster Whisper GPU or Canary |
| Translation | Qwen 3 14b+ or Gemma 3 12b |
| TTS | F5-TTS GPU |
| Voice Clone | OpenVoice V2 GPU |
| Lip Sync | **LatentSync** |
| Render | FFmpeg NVENC |

### Catalog alignment

- Runtime catalog default (LatentSync) **matches** operational default.
- Parakeet/Canary become viable STT upgrades.

---

## ENTERPRISE_GPU

```
Machine Profile: ENTERPRISE_GPU
A100 / H100 / RTX 5090 32+ GB VRAM
```

### Recommended pipeline

| Stage | Engine |
|---|---|
| Speech | Canary (multilingual) or Parakeet |
| Translation | Qwen 3 32b / remote API |
| TTS | F5-TTS or Dia |
| Voice Clone | OpenVoice V2 or XTTS-v2 |
| Lip Sync | LatentSync (+ EchoMimic alt) |
| Render | NVENC + AV1 parallel outputs |

### Remote provider

- Use REMOTE for batch scale even on enterprise hardware.
- LatentSync batch on dedicated GPU workers.

---

## Cross-profile decision matrix

| Capability | LOW_END_LOCAL | MID_RANGE_NVIDIA | HIGH_END_NVIDIA | ENTERPRISE |
|---|---|---|---|---|
| **Catalog default** | (same) | (same) | (same) | (same) |
| **Operational STT** | Faster Whisper | Faster Whisper | Faster Whisper / Canary | Canary |
| **Operational translation** | Gemma 3 4b | Gemma 3 8b | Qwen 3 14b | Qwen 3 32b |
| **Operational TTS** | F5-TTS | F5-TTS GPU | F5-TTS GPU | F5 / Dia |
| **Operational voice** | OpenVoice V2 | OpenVoice V2 | OpenVoice V2 | XTTS alt |
| **Operational lip** | **Wav2Lip** | Wav2Lip | **LatentSync** | LatentSync |
| **Operational render** | AV1 CPU | NVENC | NVENC | NVENC + AV1 |
| **Blocked future** | LatentSync | LatentSync* | — | — |

\*Blocked until 18 GB VRAM GPU.

---

## Environment variables (current defaults)

| Variable | Default | Maps to |
|---|---|---|
| `STT_PROVIDER` | faster_whisper | faster_whisper_large_v3 |
| `TRANSLATION_PROVIDER` | ollama | ollama_gemma3 |
| `TTS_PROVIDER` | f5 | f5_tts |
| `VOICE_CLONE_PROVIDER` | openvoice | openvoice_v2 |
| `LIP_SYNC_PROVIDER` | latentsync | latentsync (hardware gate → wav2lip) |
| `VIDEO_RENDER_PROVIDER` | ffmpeg | ffmpeg |

**Recommendation:** On `LOW_END_LOCAL`, set `LIP_SYNC_PROVIDER=wav2lip` in `.env` for explicit alignment with hardware pipeline (optional — intelligent provisioner already selects Wav2Lip).

---

## RAM budget (16 GB reference host)

| Component | Approx. RAM | Concurrent? |
|---|---|---|
| OS + Docker | 4–5 GB | Always |
| Ollama Gemma 3 4b | 3–4 GB | With STT |
| Faster Whisper large-v3 | 2–4 GB | Sequential preferred |
| F5-TTS / OpenVoice | 4–6 GB | **Not parallel with Ollama** |
| Wav2Lip | 4–6 GB | Sequential |
| FFmpeg AV1 encode | 2–4 GB | After AI stages |

**Operational rule:** Run pipeline stages **sequentially**, not parallel — matches Lumen orchestrator design.

---

## Summary for your decision

| Question | Answer for LOW_END_LOCAL |
|---|---|
| Keep official catalog defaults? | **Yes** — tiered product story |
| Change catalog default for lip sync? | **No** — use hardware pipeline + env override |
| Replace any engine today? | **No** — add Kokoro, SeedVC later |
| Reserve NVIDIA engines? | **Yes** — all BLOCKED entries stay |
| Run provisioning? | Wav2Lip + compatible only (separate sprint) |
