# Platform Sprint 34 — Final Verification Report

Version: 1.0

Status: Accepted

Date: 2026-06-26

---

# Executive summary

Platform Sprint 34 delivers the **AI Engine Platform** abstraction layer for Phase 2 (AI Video Localization Platform). No new user-facing video features were added — this sprint establishes a unified registry, capability resolution, read-only frontend settings, and OpenAPI documentation so future TTS, voice clone, and lip-sync providers can be added without refactoring handlers or UI.

| Area | Result |
| ---- | ------ |
| Backend PHPUnit | ✅ 1134 tests, 3828 assertions |
| Backend architecture | ✅ 36 tests, 45 assertions |
| Backend OpenAPI | ✅ 80 tests, 814 assertions |
| Frontend build | ✅ OK |
| Frontend Vitest | ✅ 558 tests (113 files) |
| Frontend Biome | ✅ clean (553 files) |
| Worker pytest | ✅ 127 tests |
| Worker Ruff | ✅ All checks passed |
| AI Engine domain | ✅ Immutable aggregate + capability enum |
| Provider registry | ✅ FasterWhisper, Ollama enabled; future providers disabled |
| Capability resolution | ✅ Handlers resolve by capability, not provider name |
| Frontend AI settings | ✅ AIEngineSettings + AIProviderList at `/settings/ai` |
| OpenAPI AI schemas | ✅ `AIEngine`, `AIProvider`, `AIEngineCapability` |

---

# Platform Sprint 34 scope (slices 01–05)

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| P34-SLICE-01 | `AIEngine`, `AIEngineCapability`, `AIEngineProvider`, `AIEngineRegistry` | ✅ |
| P34-SLICE-02 | `AIEngineRegistryFactory`, `AIProviderResolver`, provider registration | ✅ |
| P34-SLICE-03 | Capability resolution in `ProcessVideoHandler`, `VideoTranslationGenerator` | ✅ |
| P34-SLICE-04 | `AIEngineSettings`, `AIProviderList`, `AIEngineService` at `/settings/ai` | ✅ |
| P34-SLICE-05 | OpenAPI AI engine schemas, architecture docs, this report | ✅ |

---

# Final architecture

```text
Video Upload (POST /api/videos)
        │
        ▼
VideoJob (queued)
        │
        ▼
ProcessVideoMessage → ProcessVideoHandler
        ├── AIProviderResolver.resolveSpeechToText() → FasterWhisper
        ├── TranscriptRepository.save()
        ├── ArtifactRepository.create(ArtifactType::Transcript)
        ├── VideoTranslationGenerator
        │       ├── AIProviderResolver.resolveTranslation() → Ollama
        │       ├── TranslationRepository.save() × N languages
        │       └── ArtifactRepository.create(ArtifactType::Translation) × N
        └── VideoJob.complete()
        │
        ▼
GET /api/ai/providers → AIEngineSettings (/settings/ai)
```

No TTS, voice clone, lip-sync, or video rendering in this sprint.

---

# P34-SLICE-01 — AI Engine Domain

| Component | Role |
| --------- | ---- |
| `AIEngine` | Immutable aggregate per capability with provider list |
| `AIEngineId` | Value object identifying an engine |
| `AIEngineCapability` | Enum: SpeechToText, Translation, TextToSpeech, VoiceClone, LipSync |
| `AIEngineProvider` | Immutable value object: providerId, displayName, capability, enabled |
| `AIEngineConfiguration` | Configuration for enabled/disabled providers |
| `AIEngineRegistry` | Immutable registry: findByCapability, findById, enabledProviders |
| `InvalidAIEngineException` | Domain validation errors |

---

# P34-SLICE-02 — Provider Registry & Factory

| Component | Role |
| --------- | ---- |
| `AIEngineRegistryFactory` | Builds registry with all known providers |
| `AIProviderResolver` | Infrastructure implementation of domain port |
| `AIProviderResolverInterface` | Domain port: resolveSpeechToText, resolveTranslation, etc. |

Initial registry:

| Capability | Provider | Status |
| ---------- | -------- | ------ |
| SpeechToText | FasterWhisper | ✅ enabled |
| Translation | Ollama | ✅ enabled |
| TextToSpeech | F5-TTS, Kokoro, XTTS | ⏳ disabled |
| VoiceClone | OpenVoice, SeedVC | ⏳ disabled |
| LipSync | LatentSync, Wav2Lip | ⏳ disabled |

---

# P34-SLICE-03 — Capability Resolution

| Component | Role |
| --------- | ---- |
| `ProcessVideoHandler` | Uses `AIProviderResolverInterface::resolveSpeechToText()` |
| `VideoTranslationGenerator` | Uses `AIProviderResolverInterface::resolveTranslation()` |
| `CapabilityProviderResolutionTest` | Unit tests for capability lookup, fallback, disabled providers |

Application layer no longer depends on concrete provider names.

---

# P34-SLICE-04 — Frontend AI Engine Settings

| Component | Role |
| --------- | ---- |
| `AIEngineService` | Validates and delegates to repository |
| `HttpAIEngineRepository` | GET `/api/ai/providers` |
| `MockAIEngineRepository` | Mock mode support |
| `AIEngineSettings` | Read-only overview grouped by capability |
| `AIProviderList` | Provider list with enabled/coming-soon status |
| `AIEngineSettingsPage` | Route `/settings/ai` |

Feature components use `aiEngineService` only — no direct HTTP in features.

---

# P34-SLICE-05 — OpenAPI & Documentation

| Item | Location |
| ---- | -------- |
| `AIEngine` schema | `Presentation/OpenApi/Schema/AIEngineSchema.php` |
| `AIProvider` schema | `Presentation/OpenApi/Schema/AIProviderSchema.php` |
| `AIEngineCapability` schema | `Presentation/OpenApi/Schema/AIEngineCapabilitySchema.php` |
| `AIProvidersList` schema | `Presentation/OpenApi/Schema/AIProvidersListSchema.php` |
| Controller annotations | `ListAIProvidersController` |
| OpenAPI tests | 3 new tests in `ApiDocumentationTest` |
| Architecture rules | AI Engine Platform section |
| OpenAPI guide | AI engine providers section |

---

# Architectural decisions

| Decision | Rationale |
| -------- | --------- |
| `AIProviderResolverInterface` in Domain | Application must not import Infrastructure factories (architecture test) |
| Immutable `AIEngine` aggregate | Consistent with Translation, Transcript, and other domain models |
| Disabled future providers in registry | UI can show "Coming soon" without code changes when providers are implemented |
| Single `GET /api/ai/providers` endpoint | Read-only overview; future slices add selection/editing |
| Capability enum extensible | ImageGeneration, VideoGeneration can be added without structural change |
| `InvalidAIEngineConfigurationException` extends `RuntimeException` | Domain `InvalidAIEngineException` is `final`; infrastructure uses separate exception |

---

# Functional criteria

| Criterion | Status |
| --------- | ------ |
| All AI engines registered in a single registry | ✅ |
| Handlers request capability, not concrete provider | ✅ |
| Providers can be enabled/disabled by configuration | ✅ |
| Frontend displays available engines (read-only) | ✅ |
| Architecture ready for F5-TTS, Kokoro, OpenVoice, LatentSync | ✅ |

---

# Validation commands

```bash
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

# Documentation tree

```text
docs/
├── architecture/
│   ├── README.md              (Platform Sprint 34 section added)
│   ├── architecture-rules.md  (AI Engine Platform section)
│   └── openapi.md             (AI engine providers endpoint + schemas)
└── reports/
    ├── Sprint31-Verification.md
    ├── Sprint32-Verification.md
    ├── Sprint33-Verification.md
    └── Sprint34-Verification.md
```

---

# Platform capabilities after Sprint 34

| Capability | Status |
| ---------- | ------ |
| Semantic Search | ✅ |
| Vector Store | ✅ |
| Embedding Providers | ✅ |
| Chat (single-turn) | ✅ |
| Streaming (single-turn) | ✅ |
| Interactive Citations | ✅ |
| Performance Metrics | ✅ |
| Embedding Cache | ✅ |
| Persistent Conversations | ✅ |
| Multi-Document Conversations | ✅ |
| Multi-Document RAG | ✅ |
| Knowledge Graph Explorer | ✅ |
| Deterministic Agent Workflows | ✅ |
| Agent Real Tool Execution | ✅ (4 of 4 tools) |
| Agent Metadata Aggregation | ✅ |
| Video Upload | ✅ |
| Video Job Persistence | ✅ |
| Video Queue Dispatch | ✅ |
| Video Transcription | ✅ |
| Transcript Viewer | ✅ |
| Video Translation | ✅ |
| Translation Viewer | ✅ |
| **AI Engine Platform** | ✅ |
| Text-to-Speech | ⏳ Sprint 35 |
| Voice Cloning | ⏳ Sprint 36 |
| Lip-sync | ⏳ Sprint 37 |
| Video Rendering | ⏳ Sprint 38 |

---

# Sprint 34 commits

| # | Hash | Message |
| - | ---- | ------- |
| 1 | `906140f` | feat(ai): add ai engine domain |
| 2 | `2b8480c` | feat(ai): add provider registry |
| 3 | `1d02954` | refactor(ai): resolve providers by capability |
| 4 | `43162f4` | feat(frontend): add ai engine settings |
| 5 | `d40c556` | docs(ai): document ai engine platform |
