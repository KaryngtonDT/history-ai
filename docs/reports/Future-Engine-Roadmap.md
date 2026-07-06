# Future Engine Roadmap

**Date:** 2026-07-06  
**Principle:** Keep all blocked premium engines in Runtime Registry. Never delete — tier by hardware profile.

---

## Blocked engines — Future Hardware section

### LatentSync

| Field | Value |
|---|---|
| **Current status** | BLOCKED |
| **Reason** | NVIDIA CUDA required; ~18 GB VRAM |
| **Registry role** | Catalog default (lip sync) |
| **Your machine** | Incompatible — do not install |
| **Future status** | READY |
| **When** | RTX 4090 (24 GB), RTX 5090 (32 GB), A6000, remote NVIDIA worker |
| **Provider** | HOST (future) / REMOTE (now) |
| **Keep in registry?** | **Yes** — premium quality tier |
| **Why official default** | Best open lip-sync fidelity for diffusion pipeline (2024–2026) |
| **2026 recommendation** | Still top tier for NVIDIA; no demotion |

### EchoMimic V2

| Field | Value |
|---|---|
| **Current status** | BLOCKED |
| **Reason** | NVIDIA CUDA; ~12 GB VRAM |
| **Future status** | READY on RTX 3080 12 GB+ |
| **Keep?** | Yes — alt premium path |
| **vs LatentSync** | Slightly lower VRAM bar; comparable quality tier |

### NVIDIA Parakeet

| Field | Value |
|---|---|
| **Current status** | BLOCKED |
| **Reason** | NeMo toolkit + CUDA |
| **Future status** | READY on any NVIDIA datacenter GPU |
| **Keep?** | Yes — STT quality leader on NVIDIA |
| **When** | RTX 3060+ with NeMo install |

### NVIDIA Canary

| Field | Value |
|---|---|
| **Current status** | BLOCKED |
| **Reason** | NeMo + CUDA |
| **Future status** | READY on NVIDIA |
| **Keep?** | Yes — multilingual NVIDIA STT |

### FFmpeg NVENC

| Field | Value |
|---|---|
| **Current status** | BLOCKED |
| **Reason** | NVENC hardware encoder requires NVIDIA GPU |
| **Future status** | READY on any GeForce with NVENC |
| **Keep?** | Yes — 10× faster encode vs CPU |
| **When** | Any RTX / GTX 16-series+ with driver passthrough to Docker |

### Dia TTS

| Field | Value |
|---|---|
| **Current status** | BLOCKED / MISSING |
| **Reason** | 8 GB RAM minimum; no CPU fallback in matrix; manual install |
| **Future status** | READY on 32 GB RAM + GPU |
| **Keep?** | Yes as alt 2 — quality niche |

---

## Wav2Lip — operational default (not blocked)

| Field | Value |
|---|---|
| **Current status** | MISSING (hardware-compatible) |
| **Action** | Provision via intelligent provisioning — not a registry change |
| **Role** | Local lip sync on `LOW_END_LOCAL` |
| **Do not replace LatentSync in catalog** | Hardware pipeline selects Wav2Lip automatically |

---

## Alternative candidates (not yet in registry)

Ranked 1–5 per capability. **Maturity** = production-ready for localization use. **Your HW** = AMD iGPU 16 GB reference.

### Speech-to-Text

| Rank | Candidate | Maturity | Your HW | Docker | Lumen fit | Notes |
|---|---|---|---|---|---|---|
| 1 | **Whisper.cpp** | ★★★★★ | Compatible | Easy | High | GGML CPU; faster than FW on some CPUs |
| 2 | **Distil-Whisper** | ★★★★☆ | Compatible | Easy | High | Smaller, faster; slight WER trade-off |
| 3 | **Insanely Fast Whisper** | ★★★★☆ | GPU preferred | Medium | Medium | Batch throughput |
| 4 | **Moonshine** | ★★★☆☆ | Compatible | Medium | Medium | Tiny models; edge |
| 5 | **Voxtral (Mistral)** | ★★★☆☆ | Partial | API | Low | Multimodal; API-first |

**Recommendation:** Evaluate Whisper.cpp as **Alt 3** — potential CPU speed win without dropping Faster Whisper as default until benchmarked on FR/DE test set.

### Translation

| Rank | Candidate | Maturity | Your HW | Docker | Lumen fit | Notes |
|---|---|---|---|---|---|---|
| 1 | **Llama 3.3 8B** (Ollama) | ★★★★★ | Tight on 16 GB | Yes | High | Drop-in Ollama tag |
| 2 | **Mistral Small 3** | ★★★★☆ | Compatible | Yes | High | Strong EU languages |
| 3 | **Phi-4 mini** | ★★★★☆ | Compatible | Yes | Medium | Microsoft; compact |
| 4 | **Gemma 3 12B** | ★★★★☆ | Marginal RAM | Yes | High | Quality upgrade path |
| 5 | **Remote API (Gemini/Claude)** | ★★★★★ | N/A | N/A | Medium | REMOTE provider tier |

**Recommendation:** Add **Llama 3.3 8B** and **Mistral Small** as Ollama alt tags — no default change yet.

### Text-to-Speech

| Rank | Candidate | Maturity | Your HW | Docker | Lumen fit | Notes |
|---|---|---|---|---|---|---|
| 1 | **Kokoro ONNX** | ★★★★★ | Compatible | Medium | **High — integrate next** | Already in catalog; needs packaging |
| 2 | **CosyVoice 2** | ★★★★☆ | Compatible | Hard | Medium | Quality + zero-shot |
| 3 | **Fish Speech** | ★★★★☆ | GPU preferred | Hard | Medium | 2025 momentum |
| 4 | **Piper** | ★★★★★ | Compatible | Easy | High | Fast CPU; lower quality |
| 5 | **ChatTTS** | ★★★☆☆ | Compatible | Medium | Low | Conversational niche |

**Recommendation:** **Prioritize Kokoro** auto-provision — best ROI for your hardware.

### Voice Clone

| Rank | Candidate | Maturity | Your HW | Docker | Lumen fit | Notes |
|---|---|---|---|---|---|---|
| 1 | **SeedVC** | ★★★★☆ | Compatible | Medium | Medium | In AIEngineRegistry (disabled) |
| 2 | **CosyVoice (clone mode)** | ★★★★☆ | Compatible | Hard | Medium | Unified with TTS candidate |
| 3 | **GPT-SoVITS** | ★★★★☆ | GPU preferred | Hard | Low | Training-heavy |
| 4 | **RVC** | ★★★★★ | Compatible | Medium | Low | Singing focus |
| 5 | **XTTS-v2** | ★★★★★ | Compatible | Manual | Medium | Already catalog alt 2 |

**Recommendation:** Enable **SeedVC** path already stubbed in `AIEngineRegistryFactory` before adding new engines.

### Lip Sync

| Rank | Candidate | Maturity | Your HW | Docker | Lumen fit | Notes |
|---|---|---|---|---|---|---|
| 1 | **Wav2Lip** | ★★★★★ | Compatible | Yes | **In registry** | Local default |
| 2 | **LivePortrait** | ★★★★☆ | GPU preferred | Hard | Medium | 2024–2025 quality leap |
| 3 | **MuseTalk** | ★★★★☆ | CUDA | Hard | Medium | Was in catalog; replaced by Wav2Lip |
| 4 | **Video Retalking** | ★★★☆☆ | CUDA | Hard | Low | Older baseline |
| 5 | **SadTalker** | ★★★☆☆ | Compatible | Medium | Medium | CPU viable; lower fidelity |

**Recommendation:** Re-add **MuseTalk** as Alt 3 for NVIDIA tier only. **LivePortrait** as 2026 integration candidate for mid-range NVIDIA.

### Video Render

| Rank | Candidate | Maturity | Your HW | Docker | Lumen fit | Notes |
|---|---|---|---|---|---|---|
| 1 | **FFmpeg SVT-AV1** | ★★★★★ | Compatible | Easy | High | Faster CPU AV1 |
| 2 | **FFmpeg libsvtav1** | ★★★★★ | Compatible | Easy | High | Same family |
| 3 | **Hardware AV1 (Intel QSV)** | ★★★☆☆ | Partial | Hard | Low | Intel iGPU only |
| 4 | **Remote transcoding** | ★★★★★ | N/A | N/A | Medium | REMOTE provider |
| 5 | **HandBrake CLI** | ★★★★☆ | Compatible | Easy | Low | Wrapper around ffmpeg |

**Recommendation:** Add **ffmpeg_svtav1** variant to catalog before new tools.

---

## Integration roadmap (suggested phases)

### Phase A — No registry change (your machine today)

- Intelligent provision: Wav2Lip, existing READY engines
- Document LatentSync as future tier

### Phase B — Packaging (Q3 2026)

| Engine | Action |
|---|---|
| Kokoro | Auto-provision + catalog alt 1 → active |
| SeedVC | Enable in AIEngineRegistry + Runtime catalog |
| ffmpeg_svtav1 | New encoder variant |

### Phase C — NVIDIA workstation (future hardware)

| Engine | Action |
|---|---|
| LatentSync | Promote to operational default |
| FFmpeg NVENC | Enable render path |
| Parakeet / Canary | Optional STT upgrade |
| LivePortrait | Alt lip sync |

### Phase D — Remote provider

| Engine | Action |
|---|---|
| LatentSync | REMOTE execution |
| Large LLM translation | API provider |
| Cloud transcode | REMOTE render |

---

## Engines explicitly NOT recommended for removal

| Engine | Rationale |
|---|---|
| LatentSync | Premium default; product differentiator on NVIDIA |
| EchoMimic V2 | Valid alt premium |
| Parakeet / Canary | NVIDIA STT tier |
| FFmpeg NVENC | Standard fast path |
| XTTS-v2 | Quality manual override |
| Dia | Quality niche |

---

## MuseTalk note

MuseTalk was **removed from Runtime catalog** (replaced by Wav2Lip in Sprint 70.45). For roadmap purposes:

- **Re-integration candidate** as NVIDIA-only Alt 3
- Not required for your current machine
- Do not conflate with Wav2Lip local role
