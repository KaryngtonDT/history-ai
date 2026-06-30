# Platform Sprint 39 — Final Verification Report

Version: 1.0

Status: Accepted

Date: 2026-06-26

---

# Executive summary

Platform Sprint 39 delivers **AI Engine Selector & Pipeline Configuration** for Phase 2. Users can choose the AI provider for each pipeline stage (speech-to-text, translation, TTS, voice clone, lip sync, video render), save configuration, reset to defaults, and have processing jobs resolve providers from that configuration at runtime.

| Area | Result |
| ---- | ------ |
| Backend PHPUnit | ✅ |
| Backend architecture | ✅ |
| Backend OpenAPI | ✅ |
| Frontend build | ✅ |
| Frontend Vitest | ✅ |
| Frontend Biome | ✅ |
| Worker pytest | ✅ |
| Worker Ruff | ✅ |
| Pipeline domain | ✅ Immutable aggregate + validation |
| Persistence | ✅ Doctrine `pipeline_configuration` table |
| Runtime resolution | ✅ `AIProviderResolver` + registry fallback |
| Frontend pipeline builder | ✅ `/settings/pipeline` |
| OpenAPI pipeline schemas | ✅ `PipelineConfiguration`, `PipelineStage` |

---

# Platform Sprint 39 scope (slices 01–05)

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| P39-SLICE-01 | `PipelineConfiguration`, `PipelineStage`, `PipelineStageType` domain | ✅ |
| P39-SLICE-02 | Doctrine persistence, save/load/reset handlers, REST endpoints | ✅ |
| P39-SLICE-03 | `AIProviderResolver` reads pipeline config with registry fallback | ✅ |
| P39-SLICE-04 | `PipelineBuilder`, `PipelineStageSelector`, `PipelineService` | ✅ |
| P39-SLICE-05 | OpenAPI pipeline schemas, architecture docs, this report | ✅ |

---

# Final architecture

```text
Upload Video
        │
        ▼
PipelineBuilder (/settings/pipeline)
        │
        ├── Speech-to-Text     → FasterWhisper (configurable)
        ├── Translation        → Ollama / Qwen (configurable)
        ├── Text-to-Speech     → F5-TTS (configurable)
        ├── Voice Clone        → OpenVoice (configurable)
        ├── Lip Sync           → LatentSync (configurable)
        └── Video Render       → FFmpeg (configurable)
        │
        ▼
ProcessVideoHandler
        │
        ▼
AIProviderResolver (pipeline config → registry default fallback)
        │
        ▼
Final MP4
```

---

# P39-SLICE-01 — Pipeline Configuration Domain

| Component | Role |
| --------- | ---- |
| `PipelineStageType` | Enum: six mandatory pipeline stages |
| `PipelineStage` | Immutable: stage + providerId |
| `PipelineStageCollection` | Ordered collection of stages |
| `PipelineConfiguration` | Aggregate: `providerFor()`, `replace()`, `validate()` |
| `PipelineConfigurationRepositoryInterface` | Port: save, findLatest, deleteAll |

---

# P39-SLICE-02 — Configuration Persistence

| Component | Role |
| --------- | ---- |
| `DoctrinePipelineConfigurationRepository` | Persists latest configuration with version and timestamps |
| `SavePipelineConfigurationHandler` | Validates enabled providers and saves |
| `LoadPipelineConfigurationHandler` | Returns latest or platform defaults |
| `ResetPipelineConfigurationHandler` | Deletes saved config and returns defaults |
| REST | `GET/PUT /api/pipeline`, `POST /api/pipeline/reset` |

---

# P39-SLICE-03 — Runtime Provider Resolution

| Behavior | Detail |
| -------- | ------ |
| Configured provider | `AIProviderResolver` reads `findLatest()` pipeline configuration |
| Fallback | Uses `AIEngineConfiguration` defaults when no saved config |
| Override | Explicit provider in request still takes precedence |
| No regression | Existing tests pass with empty pipeline repository |

---

# P39-SLICE-04 — Frontend Pipeline Builder

| Component | Role |
| --------- | ---- |
| `PipelineRepository` / `HttpPipelineRepository` | Repository pattern for pipeline API |
| `PipelineService` | Load, save, reset configuration |
| `PipelineBuilder` | Full pipeline editor with save-as-default checkbox |
| `PipelineStageSelector` | Per-stage dropdown (enabled providers only) |
| Route | `/settings/pipeline` |

---

# P39-SLICE-05 — OpenAPI & Documentation

| Item | Detail |
| ---- | ------ |
| Operations | `getPipelineConfiguration`, `savePipelineConfiguration`, `resetPipelineConfiguration` |
| Schemas | `PipelineConfiguration`, `PipelineStage`, `PipelineStageType`, `SavePipelineConfigurationRequest` |
| Docs | `README.md`, `docs/architecture/README.md`, `openapi.md`, `architecture-rules.md` |

---

# Functional criteria

| Criterion | Status |
| --------- | ------ |
| User selects AI engine per stage | ✅ |
| Configuration saved and restored | ✅ |
| Disabled providers not offered in UI | ✅ |
| Pipeline uses user config at runtime | ✅ |
| Reset to defaults available | ✅ |
| Resolution via AI Engine Platform | ✅ |

---

# Next sprint

**Sprint 40 — Automatic Agent Mode**: user chooses between manual engine selection (Sprint 39) and automatic orchestration where the agent picks optimal providers per video.
