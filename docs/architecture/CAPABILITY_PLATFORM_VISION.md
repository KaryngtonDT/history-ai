# Capability Platform Vision

**Lumen evolves from a video translation pipeline into a capability-based AI platform.**

Each capability exposes a **default engine**, **alternatives**, **hardware tiers**, and **maturity level**. Runtime discovers, verifies, benchmarks, and provisions engines intelligently based on cached hardware reports.

---

## Principles

1. **Configured. Verified. Measured. Intelligent. Explainable.**
2. Defaults must match what the pipeline actually runs — no silent `deterministic` fallbacks.
3. Hardware-incompatible engines are **blocked with human-readable reasons** and a **recommended alternative**.
4. New capabilities (OCR, Vision, Embeddings, Reranking) register alongside the video pipeline without breaking existing stages.

---

## Capability Maturity

| Capability | Maturity | Default | Notable alternatives |
|------------|----------|---------|----------------------|
| Speech-to-Text | Stable | Faster Whisper Large V3 | Whisper.cpp (CPU), Parakeet/Canary (NVIDIA) |
| Translation | Stable | Ollama + Gemma 3 | Qwen 3, DeepSeek R1 Distill |
| Text-to-Speech | Stable | F5-TTS | Kokoro (CPU), Piper (lightweight), Dia (experimental) |
| Voice Clone | Stable | OpenVoice V2 | Chatterbox, XTTS-v2 |
| Lip Sync | Beta | LatentSync (premium NVIDIA) | LivePortrait, Wav2Lip (CPU), MuseTalk (legacy), EchoMimic (experimental) |
| Video Render | Stable | FFmpeg | NVENC, AV1 |
| OCR | Beta | PaddleOCR | EasyOCR |
| Vision | Experimental | Florence-2 | Qwen2.5-VL, SmolVLM |
| Embeddings | Beta | BGE-M3 | Nomic, Jina, E5 Large |
| Reranking | Beta | BGE Reranker | Jina Reranker |

API: `GET /api/runtime/capabilities/maturity`

---

## Engine Tiers

| Tier | Meaning |
|------|---------|
| `default` | Recommended default for the capability |
| `cpu_alternative` | Runs on CPU-only or low-end hosts |
| `lightweight` | Minimal RAM/VRAM footprint |
| `premium_nvidia` | Best quality; requires NVIDIA + VRAM |
| `experimental` | Early / unstable |
| `legacy` | Maintained for compatibility only |
| `alternative` | General alternative slot |

---

## Speech-to-Text

- **Default:** `faster_whisper_large_v3` — GPU optional, HuggingFace `large-v3`.
- **CPU alternative:** `whisper_cpp` — Whisper.cpp for hosts without CUDA.
- **NVIDIA premium:** `parakeet`, `canary` — NeMo models, manual NGC download.

Install: `bash /opt/lumen/install-whisper-cpp.sh`

---

## Text-to-Speech

- **Default:** `f5_tts`
- **CPU:** `kokoro`
- **Lightweight:** `piper` — `bash /opt/lumen/install-piper.sh`
- **Experimental:** `dia`

---

## Lip Sync

| Engine | Tier | Hardware |
|--------|------|----------|
| LatentSync | Premium NVIDIA | ≥18 GB VRAM, CUDA |
| LivePortrait | Alternative | ≥8 GB VRAM, CUDA |
| Wav2Lip | CPU alternative | CPU fallback, 6 GB RAM |
| MuseTalk | Legacy | NVIDIA, 8 GB VRAM |
| EchoMimic V2 | Experimental | NVIDIA, 12 GB VRAM |

Low-end local profile recommends **Wav2Lip**. Mid-range NVIDIA recommends **LivePortrait**. High-end recommends **LatentSync**.

---

## OCR

- **PaddleOCR** — document scans, CPU-friendly.
- **EasyOCR** — GPU optional, multi-language.

---

## Vision

- **Florence-2** — captioning / grounding, CPU fallback.
- **Qwen2.5-VL** — multimodal, NVIDIA 12+ GB VRAM.
- **SmolVLM** — lightweight VLM.

---

## Embeddings

- **BGE-M3** — multilingual retrieval default.
- **Nomic Embed**, **Jina Embeddings**, **E5 Large** — alternatives for Second Brain / semantic search.

---

## Reranking

- **BGE Reranker**, **Jina Reranker** — rerank retrieval candidates before LLM context.

---

## Runtime integration

```
EngineCatalogDefinitions  → 33 engines, 10 capabilities
EngineRequirementMatrix   → hardware gates per engine
CapabilityMaturityRegistry → maturity API + UI table
IntelligentEngineProvisioner → provision compatible only (cached hardware report)
```

Video pipeline stages remain: STT → Translation → TTS → Voice Clone → Lip Sync → Render.

Extended capabilities are catalogued for discovery, benchmarking, and future Shadow / Second Brain features.

---

## References

- [TASK-0071](../../planning/Platform/Sprint-71/TASK-0071.md)
- [Sprint 70.45 Hardware Profiles](../reports/Sprint70_45-Hardware-Profiles.md)
- [Runtime Technology Review](../reports/Runtime-Technology-Review.md)
