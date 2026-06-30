# Platform Sprint 36 — Final Verification Report

Version: 1.0

Status: Accepted

Date: 2026-06-26

---

# Executive summary

Platform Sprint 36 delivers **Voice Cloning Foundation** for Phase 2 (AI Video Localization Platform). Users can clone the original speaker voice onto translated generic audio using OpenVoice V2, compare original vs cloned playback, and persist cloned artifacts. SeedVC remains registered but disabled.

| Area | Result |
| ---- | ------ |
| Backend PHPUnit | ✅ 1193 tests, 3986 assertions |
| Backend architecture | ✅ 36 tests, 45 assertions |
| Backend OpenAPI | ✅ 88 tests, 845 assertions |
| Frontend build | ✅ OK |
| Frontend Vitest | ✅ 567 tests (117 files) |
| Frontend Biome | ✅ clean (585 files) |
| Worker pytest | ✅ 127 tests |
| Worker Ruff | ✅ All checks passed |
| Voice clone domain | ✅ Separate capability from TTS |
| OpenVoice provider | ✅ Enabled via AI Engine; SeedVC disabled |
| Voice clone worker | ✅ `VideoVoiceCloneGenerator` + artifact persistence |
| Frontend voice clone | ✅ `VoiceClonePanel` + compare mode at `/video/:videoId/voice-clone` |
| OpenAPI voice clone schemas | ✅ `VoiceProfile`, `VoiceCloneArtifact`, `VoiceCloneProvider` |

---

# Platform Sprint 36 scope (slices 01–05)

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| P36-SLICE-01 | `VoiceProfile`, `VoiceCloneArtifact`, `VoiceCloneProviderInterface` | ✅ |
| P36-SLICE-02 | `OpenVoiceProvider`, factory, AI Engine integration | ✅ |
| P36-SLICE-03 | `VideoVoiceCloneGenerator`, voice clone artifacts, REST endpoints | ✅ |
| P36-SLICE-04 | `VoiceClonePanel`, `VoiceModeSelector`, `VoiceCloneService` | ✅ |
| P36-SLICE-05 | OpenAPI voice clone schemas, architecture docs, this report | ✅ |

---

# Final architecture

```text
Video Upload (POST /api/videos)
        │
        ▼
ProcessVideoHandler
        ├── Transcript → Translation
        ├── (GENERATE_AUDIO=true) VideoAudioGenerator → F5-TTS
        └── (GENERATE_VOICE_CLONE=true) VideoVoiceCloneGenerator → OpenVoice
        │
        ▼
GET/POST /api/videos/{videoId}/voice-clone → VoiceClonePanel
```

Voice cloning is a **separate AI capability** — not a `TextToSpeechProvider`.

---

# P36-SLICE-01 — Voice Clone Domain

| Component | Role |
| --------- | ---- |
| `VoiceProfile` | Immutable: profileId, sourceLanguage, duration, sampleRate |
| `VoiceCloneArtifact` | Immutable: artifactId, profile, provider, clonedAudioId, storage |
| `VoiceCloneProvider` | Enum: OpenVoice, SeedVC, Mock |
| `VoiceCloneProviderInterface` | Domain port: `cloneVoice(AudioArtifact, Translation)` |
| `AIEngineCapability::VoiceClone` | Extended capability (already in registry from Sprint 34) |

---

# P36-SLICE-02 — OpenVoice V2 Provider

| Component | Role |
| --------- | ---- |
| `OpenVoiceProvider` | Infrastructure implementation of voice clone port |
| `FixedOpenVoiceProcessRunner` | Test/dev process runner |
| `VoiceCloneMapper` | Maps process output to `VoiceCloneArtifact` |
| `VoiceCloneProviderFactory` | Creates provider by configuration |
| `AIProviderResolver::resolveVoiceClone()` | Capability resolution via AI Engine |

Registry after Sprint 36:

| Capability | Provider | Status |
| ---------- | -------- | ------ |
| TextToSpeech | F5-TTS | ✅ enabled |
| VoiceClone | OpenVoice V2 | ✅ enabled |
| VoiceClone | SeedVC | ⏳ disabled |

Configuration: `VOICE_CLONE_PROVIDER=openvoice`, `OPENVOICE_MODEL=openvoice_v2`, `OPENVOICE_PATH=/models/openvoice`

---

# P36-SLICE-03 — Voice Clone Worker

| Component | Role |
| --------- | ---- |
| `VideoVoiceCloneGenerator` | Translation + generic audio → OpenVoice → persistence |
| `VoiceCloneReferenceContextInterface` | Binds original video storage path for reference audio |
| `DoctrineVoiceCloneRepository` | Persists voice clone metadata |
| `ProcessVideoHandler` | Calls generator when `GENERATE_VOICE_CLONE=true` |

REST endpoints:

| Method | Path |
| ------ | ---- |
| GET | `/api/videos/{videoId}/voice-clone` |
| POST | `/api/videos/{videoId}/voice-clone` |
| GET | `/api/videos/{videoId}/voice-clone/{language}` |
| GET | `/api/videos/{videoId}/voice-clone/{language}/stream` |

---

# P36-SLICE-04 — Frontend Voice Clone

| Component | Role |
| --------- | ---- |
| `VoiceCloneService` | Validates and delegates to repository |
| `VoiceModeSelector` | Generic vs Clone Original Voice toggle |
| `VoiceClonePanel` | Provider selection, generate, compare mode, dual players |
| `VideoVoiceClonePage` | Route `/video/:videoId/voice-clone` |

Feature components use `voiceCloneService` only — no direct HTTP in features.

---

# P36-SLICE-05 — OpenAPI & Documentation

| Item | Location |
| ---- | -------- |
| `VoiceCloneProvider` schema | `Presentation/OpenApi/Schema/VoiceCloneProviderSchema.php` |
| `VoiceProfile` schema | `Presentation/OpenApi/Schema/VoiceProfileSchema.php` |
| `VoiceCloneArtifact` schema | `Presentation/OpenApi/Schema/VoiceCloneArtifactSchema.php` |
| Controller annotations | List/Get/Generate voice clone controllers |
| OpenAPI tests | 4 new tests in `ApiDocumentationTest` |

---

# Architectural decisions

| Decision | Rationale |
| -------- | --------- |
| Voice clone as separate capability | Cloning is not TTS; distinct provider interface and AI capability |
| `VoiceCloneReferenceContextInterface` in Domain | Application must not import Infrastructure processing context |
| OpenVoice V2 primary, SeedVC disabled | Same pattern as F5-TTS/Kokoro from Sprint 35 |
| `GENERATE_VOICE_CLONE=false` by default | Requires generic audio first; opt-in in worker |
| Compare mode in UI | Side-by-side original (generic) vs cloned preview |

---

# Functional criteria

| Criterion | Status |
| --------- | ------ |
| User chooses generic vs cloned voice | ✅ |
| OpenVoice V2 is active engine | ✅ |
| SeedVC ready via same capability | ✅ |
| Cloned voices persisted as artifacts | ✅ |
| Original vs cloned comparison available | ✅ |
| Integration via AI Engine Platform | ✅ |

---

# Validation commands

```bash
docker compose build backend && docker compose up -d backend
docker compose exec backend php bin/phpunit
docker compose exec backend php bin/phpunit tests/Architecture
docker compose exec backend php bin/phpunit tests/Functional/OpenApi

npm run build
npm test
npm run check

docker compose exec worker pytest
docker compose exec worker ruff check .
```

All commands passed on 2026-06-26.

---

# Sprint 36 commits

| # | Hash | Message |
| - | ---- | ------- |
| 1 | `92e6a6c` | feat(voice): add voice cloning domain |
| 2 | `b057ebe` | feat(voice): integrate openvoice provider |
| 3 | `26088e6` | feat(worker): generate cloned audio |
| 4 | `8ae7e67` | feat(frontend): add voice cloning |
| 5 | *(this commit)* | docs(voice): document voice cloning foundation |

---

# Platform capabilities after Sprint 36

| Capability | Status |
| ---------- | ------ |
| Video Upload | ✅ |
| Video Transcription | ✅ |
| Video Translation | ✅ |
| AI Engine Platform | ✅ |
| Text-to-Speech | ✅ |
| **Voice Cloning** | ✅ |
| Lip-sync | ⏳ Sprint 37 |
| Video Rendering | ⏳ Sprint 38 |
