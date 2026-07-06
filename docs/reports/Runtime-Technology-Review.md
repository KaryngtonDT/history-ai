# Lumen Runtime — Technology Review

**Date:** 2026-07-06  
**Type:** Read-only architectural audit — no installation, no code changes  
**Post-completion report:** [Runtime-Technology-Review-After70_6.md](./Runtime-Technology-Review-After70_6.md) (live scores after Sprint 70.6 dashboard + 70.7 completion)  
**Reference machine:** `LOW_END_LOCAL` — AMD Radeon integrated GPU, no CUDA, ~16 GB RAM (~4–5 GB free), Docker Desktop / WSL2  
**Registry source:** `EngineCatalogDefinitions` (33 engines across 10 capabilities)

---

## Executive summary

Lumen's Runtime Registry is **well-structured for a tiered hardware strategy**: CPU-first defaults for local development, NVIDIA-only premium engines retained for future high-end machines and remote GPU providers.

| Verdict | Recommendation |
|---|---|
| **Keep as official defaults** | Faster Whisper, Ollama+Gemma 3, F5-TTS, OpenVoice V2, FFmpeg |
| **Keep blocked, do not remove** | LatentSync, EchoMimic V2, Parakeet, Canary, FFmpeg NVENC |
| **Promote for local lip sync** | Wav2Lip (operational default on `LOW_END_LOCAL`, catalog still lists LatentSync as nominal default) |
| **Integrate later (candidates)** | Kokoro TTS, CosyVoice, Whisper.cpp / Distil-Whisper, LivePortrait |
| **Defer or remote-only** | Dia TTS, NVIDIA NeMo STT stack |

The **catalog default** (LatentSync for lip sync) and the **hardware-recommended pipeline** (Wav2Lip on low-end) are intentionally divergent — this is correct product design, not a bug.

---

## Registry inventory

| # | Engine ID | Display name | Capability | Catalog role | Auto-provision |
|---|---|---|---|---|---|
| 1 | `faster_whisper_large_v3` | Faster Whisper Large V3 | Speech-to-Text | Default | Yes |
| 2 | `parakeet` | NVIDIA Parakeet | Speech-to-Text | Alt 1 | No |
| 3 | `canary` | NVIDIA Canary | Speech-to-Text | Alt 2 | No |
| 4 | `ollama_gemma3` | Ollama + Gemma 3 | Translation | Default | Yes |
| 5 | `ollama_qwen3` | Ollama + Qwen 3 | Translation | Alt 1 | Yes |
| 6 | `ollama_deepseek_r1_distill` | Ollama + DeepSeek R1 Distill | Translation | Alt 2 | Yes |
| 7 | `f5_tts` | F5-TTS | Text-to-Speech | Default | Yes |
| 8 | `kokoro` | Kokoro TTS | Text-to-Speech | Alt 1 | No |
| 9 | `dia` | Dia | Text-to-Speech | Alt 2 | No |
| 10 | `openvoice_v2` | OpenVoice V2 | Voice Clone | Default | Yes |
| 11 | `chatterbox` | Chatterbox | Voice Clone | Alt 1 | No |
| 12 | `xtts_v2` | XTTS-v2 | Voice Clone | Alt 2 | No |
| 13 | `latentsync` | LatentSync | Lip Sync | Default | Yes* |
| 14 | `echomimic_v2` | EchoMimic V2 | Lip Sync | Alt 1 | No |
| 15 | `wav2lip` | Wav2Lip | Lip Sync | Alt 2 | Yes |
| 16 | `ffmpeg` | FFmpeg | Video Render | Default | Yes |
| 17 | `ffmpeg_nvenc` | FFmpeg NVENC | Video Render | Alt 1 | Yes* |
| 18 | `ffmpeg_av1` | FFmpeg AV1 | Video Render | Alt 2 | Yes |

\*Provision attempted only when hardware-compatible; blocked by `ProvisioningCompatibilityGate` on `LOW_END_LOCAL`.

---

## Per-capability analysis

### 1. Speech-to-Text

#### Registered engines

| Engine | Status (your machine) | Provider | HW compat | Why |
|---|---|---|---|---|
| **Faster Whisper Large V3** | READY* | HOST | Compatible | CPU fallback; CTranslate2 int8 |
| NVIDIA Parakeet | BLOCKED | — | Incompatible | NVIDIA CUDA required; NeMo NGC |
| NVIDIA Canary | BLOCKED | — | Incompatible | NVIDIA CUDA required; NeMo NGC |

#### Why Faster Whisper is the official default

- Best open-source balance of **WER quality / install complexity / CPU viability** in 2024–2026.
- Native fit for Lumen: CLI wrapper, HuggingFace cache, no GPU gate.
- `large-v3` remains competitive with dedicated NVIDIA STT for most localization workloads.

#### Still recommended in 2026?

**Yes** as default. Alternatives exist (see `Future-Engine-Roadmap.md`) but none justify replacing the default without measured WER benchmarks on your FR/DE content.

#### Comparison (registered)

| Criterion | Faster Whisper | Parakeet | Canary |
|---|---|---|---|
| Quality | ★★★★☆ | ★★★★★ | ★★★★★ |
| CPU viable | ★★★★☆ | ☆☆☆☆☆ | ☆☆☆☆☆ |
| AMD compat | ★★★★★ | ☆☆☆☆☆ | ☆☆☆☆☆ |
| Lumen integration | ★★★★★ | ★★☆☆☆ | ★★☆☆☆ |
| Install ease | ★★★★☆ | ★☆☆☆☆ | ★☆☆☆☆ |

**Decision:** Keep Faster Whisper default. Keep Parakeet/Canary as **future NVIDIA tier** engines.

---

### 2. Translation

#### Registered engines

| Engine | Status (your machine) | Provider | HW compat | Why |
|---|---|---|---|---|
| **Ollama + Gemma 3** | READY* | DOCKER (Ollama) | Compatible | 4b quant fits 16 GB host |
| Ollama + Qwen 3 | READY* | DOCKER | Compatible | Alternative style/tone |
| Ollama + DeepSeek R1 Distill | READY* | DOCKER | Compatible | Reasoning-heavy tasks |

#### Why Ollama + Gemma 3 is default

- Lightweight, multilingual, good instruction following for localization prompts.
- Decoupled model lifecycle (pull tags) from backend image.
- Aligns with `TRANSLATION_PROVIDER=ollama` env defaults.

#### Still recommended in 2026?

**Yes.** Gemma 3 4B remains a strong local default. Qwen 3 is a valid alternative for different linguistic bias. DeepSeek R1 distill is niche (reasoning chains) — keep as alt, not default.

#### Comparison

| Criterion | Gemma 3 | Qwen 3 | DeepSeek R1 Distill |
|---|---|---|---|
| Quality (localization) | ★★★★☆ | ★★★★☆ | ★★★☆☆ |
| Speed (CPU) | ★★★★☆ | ★★★★☆ | ★★★☆☆ |
| RAM (4b) | ★★★★★ | ★★★★★ | ★★★★☆ |
| Lumen integration | ★★★★★ | ★★★★★ | ★★★★★ |

**Decision:** Keep Gemma 3 default. No replacement required.

---

### 3. Text-to-Speech

#### Registered engines

| Engine | Status (your machine) | Provider | HW compat | Why |
|---|---|---|---|---|
| **F5-TTS** | READY* | HOST | Compatible | CPU torch 2.4; real weights |
| Kokoro TTS | MISSING | — | Compatible | Not packaged; manual install |
| Dia | BLOCKED/MISSING | — | Partially incompatible | CPU fallback false in matrix; 8 GB RAM |

#### Why F5-TTS is default

- Zero-shot voice style from reference audio — matches localization pipeline.
- Proven install path in Lumen (`install-gpu-engines.sh`).
- Active community (SWivid); MIT-ish ecosystem.

#### Still recommended in 2026?

**Yes**, with **Kokoro as priority integration candidate** — smaller, faster on CPU, ONNX path, strong 2025–2026 adoption for local TTS.

#### Comparison

| Criterion | F5-TTS | Kokoro | Dia |
|---|---|---|---|
| Quality | ★★★★☆ | ★★★★☆ | ★★★★★ |
| CPU speed | ★★★☆☆ | ★★★★★ | ★★☆☆☆ |
| Install (Lumen) | ★★★★☆ | ★★☆☆☆ | ★★☆☆☆ |
| AMD compat | ★★★★☆ | ★★★★★ | ★★☆☆☆ |

**Decision:** Keep F5 default. **Integrate Kokoro** as auto-provision alt when packaging story is ready.

---

### 4. Voice Clone

#### Registered engines

| Engine | Status (your machine) | Provider | HW compat | Why |
|---|---|---|---|---|
| **OpenVoice V2** | READY* | HOST | Compatible | CPU inference validated |
| Chatterbox | MISSING | — | Compatible* | Manual install only |
| XTTS-v2 | MISSING | — | Compatible* | Coqui license + manual |

#### Why OpenVoice V2 is default

- Official V2 HF weights; tone-color conversion fits dubbing workflow.
- Only voice-clone engine with **full Lumen install + smoke test** path today.

#### Still recommended in 2026?

**Yes** for production default. **CosyVoice / SeedVC** are integration candidates for quality or speed trade-offs.

#### Known friction

- UniDic (~775 MB) pulled via MeloTTS import chain — documented, not required for EN/FR/DE inference.
- Install weight and RAM spikes during provisioning on 16 GB hosts.

#### Comparison

| Criterion | OpenVoice V2 | Chatterbox | XTTS-v2 |
|---|---|---|---|
| Quality | ★★★★☆ | ★★★★☆ | ★★★★★ |
| CPU viable | ★★★☆☆ | ★★★☆☆ | ★★★☆☆ |
| Lumen integration | ★★★★★ | ★☆☆☆☆ | ★☆☆☆☆ |
| License clarity | ★★★★☆ | ★★★☆☆ | ★★★☆☆ |

**Decision:** Keep OpenVoice V2 default. Keep XTTS as premium manual alt.

---

### 5. Lip Sync

#### Registered engines

| Engine | Status (your machine) | Provider | HW compat | Why |
|---|---|---|---|---|
| **LatentSync** | BLOCKED | HOST | Incompatible | NVIDIA CUDA + ~18 GB VRAM |
| EchoMimic V2 | BLOCKED | — | Incompatible | NVIDIA CUDA + ~12 GB VRAM |
| **Wav2Lip** | MISSING* | HOST | Compatible | CPU fallback; not yet installed |

\*Hardware-compatible; awaiting intelligent provision.

#### Why LatentSync is catalog default (not operational default on your PC)

- State-of-the-art lip fidelity for diffusion-based sync (ByteDance).
- Product positioning: **quality tier** for NVIDIA workstations.
- Pipeline planner prefers LatentSync on `HIGH_END_NVIDIA` / `ENTERPRISE_GPU`.

#### Still recommended in 2026?

**Yes as premium default** — do not demote in registry. On your machine, **Wav2Lip is the operational default** per `HardwareReportBuilder`.

#### Comparison

| Criterion | LatentSync | EchoMimic V2 | Wav2Lip |
|---|---|---|---|
| Quality | ★★★★★ | ★★★★☆ | ★★★☆☆ |
| CPU viable | ☆☆☆☆☆ | ☆☆☆☆☆ | ★★★★☆ |
| VRAM need | ★☆☆☆☆ (18 GB) | ★★☆☆☆ (12 GB) | ★★★★★ |
| Lumen integration | ★★★★☆ | ★★☆☆☆ | ★★★★☆ |
| Project maturity | ★★★★☆ | ★★★☆☆ | ★★★★★ |

**Decision:** **Do not remove LatentSync.** Keep blocked. Use Wav2Lip locally; LatentSync on RTX 4090+ or REMOTE.

---

### 6. Video Render

#### Registered engines

| Engine | Status (your machine) | Provider | HW compat | Why |
|---|---|---|---|---|
| **FFmpeg** | READY | HOST | Compatible | Bundled in image |
| FFmpeg NVENC | BLOCKED | HOST | Incompatible | NVENC requires NVIDIA |
| FFmpeg AV1 | READY* | HOST | Compatible | CPU libaom (slow) |

#### Why FFmpeg is default

- Universal mux/encode; zero model weight.
- NVENC and AV1 are **encoder variants**, not separate products.

#### Still recommended in 2026?

**Yes.** Add **SVT-AV1** as future candidate for faster CPU AV1.

**Decision:** Keep FFmpeg default. NVENC blocked locally. AV1 for quality-first local export.

---

## Cross-cutting: Provider model

| Provider | Meaning in Lumen | Your machine |
|---|---|---|
| **HOST** | Python venv + CLI on mounted `/models` | Primary for STT, TTS, voice, lip, render |
| **DOCKER** | Ollama sidecar | Translation |
| **REMOTE** | Future GPU cloud / API | LatentSync, NVENC, NeMo STT |

---

## Decision framework (your questions)

| Question | Answer |
|---|---|
| Which engines to keep as official? | All 18 — tiered by hardware profile |
| Which to replace? | None immediately; **add** Kokoro, CosyVoice candidates |
| New alternatives to integrate? | See `Future-Engine-Roadmap.md` |
| Reserve for future NVIDIA? | LatentSync, EchoMimic, Parakeet, Canary, NVENC |
| Pipeline per profile? | See `Hardware-Recommendation-Matrix.md` |

---

## Related documents

| Document | Purpose |
|---|---|
| [Engine-Ranking.md](./Engine-Ranking.md) | Star ratings per engine |
| [Engine-Comparison-Matrix.md](./Engine-Comparison-Matrix.md) | Side-by-side tables |
| [Future-Engine-Roadmap.md](./Future-Engine-Roadmap.md) | Blocked engines + 5 candidates per capability |
| [Hardware-Recommendation-Matrix.md](./Hardware-Recommendation-Matrix.md) | Pipelines by machine profile |

---

## Audit methodology

- Source: `EngineCatalogDefinitions`, `EngineRequirementMatrix`, `EngineProvisioningCatalog`, `AIEngineRegistryFactory`, Sprint 70.45 verification
- Hardware assumptions: documented reference PC (AMD iGPU, 16 GB RAM, no CUDA)
- No runtime execution, no installs, no registry mutations
- External landscape: industry consensus for 2026 open-source AI video stack
