# Platform Sprint 32 — Final Verification Report

Version: 1.0

Status: Accepted

Date: 2026-06-26

---

# Executive summary

Platform Sprint 32 delivers the **Speech-to-Text Foundation** for Phase 2 (AI Video Localization Platform). Uploaded videos are transcribed via Faster-Whisper, persisted as domain transcripts, exposed as transcript artifacts, and viewable in the frontend. Slice 5 changed **OpenAPI documentation, architecture docs, and verification only** — no business logic in backend handlers, frontend, or worker.

| Area | Result |
| ---- | ------ |
| Backend PHPUnit | ✅ 1067 tests, 3599 assertions |
| Backend architecture | ✅ 36 tests, 45 assertions |
| Backend OpenAPI | ✅ 64 tests, 713 assertions |
| Frontend build | ✅ OK |
| Frontend Vitest | ✅ 549 tests (108 files) |
| Frontend Biome | ✅ clean (519 files) |
| Worker pytest | ✅ 127 tests |
| Worker Ruff | ✅ All checks passed |
| Transcript domain | ✅ Immutable aggregate + segments |
| Faster-Whisper provider | ✅ Factory + parser + mapping |
| Worker integration | ✅ ProcessVideoHandler → artifact |
| Frontend transcript viewer | ✅ TranscriptPanel + TranscriptTimeline |
| OpenAPI transcript schemas | ✅ `Transcript`, `TranscriptSegment`, `TranscriptLanguage` |

---

# Platform Sprint 32 scope (slices 01–05)

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| P32-SLICE-01 | `Transcript`, `TranscriptSegment`, `TranscriptLanguage`, `SpeechToTextProviderInterface` | ✅ |
| P32-SLICE-02 | `FasterWhisperProvider`, `SpeechToTextProviderFactory`, output parser | ✅ |
| P32-SLICE-03 | `ProcessVideoHandler`, transcript persistence, transcript artifact | ✅ |
| P32-SLICE-04 | `TranscriptPanel`, `TranscriptTimeline`, `TranscriptService` | ✅ |
| P32-SLICE-05 | OpenAPI transcript schemas, architecture docs, this report | ✅ |

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
        ├── VideoJob.startProcessing()
        ├── SpeechToTextProviderFactory → FasterWhisperProvider
        ├── TranscriptRepository.save()
        ├── ArtifactRepository.create(ArtifactType::Transcript)
        └── VideoJob.complete()
        │
        ▼
GET /api/videos/{videoId}/transcript
        │
        ▼
TranscriptPanel (/video/:videoId/transcript)
        └── TranscriptService → HttpTranscriptRepository
```

No translation, TTS, lip-sync, or embeddings in this sprint.

---

# P32-SLICE-01 — Speech-to-Text Domain

| Component | Role |
| --------- | ---- |
| `Transcript` | Immutable aggregate: `text()`, `duration()`, `segmentCount()` |
| `TranscriptSegment` | Timestamped segment with validation (`end >= start`, non-empty text) |
| `TranscriptSegmentCollection` | Ordered immutable collection |
| `TranscriptLanguage` | Enum: English, French, German, Unknown |
| `SpeechToTextProviderInterface` | Port: `transcribe(VideoJob): Transcript` |
| `InvalidTranscriptException` | Domain validation errors |

Tests: 18 unit tests under `tests/Unit/Domain/Speech/`.

---

# P32-SLICE-02 — Faster-Whisper Provider

| Component | Role |
| --------- | ---- |
| `FasterWhisperProvider` | Invokes faster-whisper CLI, maps output to `Transcript` |
| `FasterWhisperOutputParser` | Parses JSON stdout into domain segments |
| `SpeechToTextProviderFactory` | Wires `STT_PROVIDER` env to concrete provider |
| `ShellFasterWhisperProcessRunner` | Production process invocation |
| `FixedFasterWhisperProcessRunner` | Deterministic JSON for test env |
| `TranscriptLanguageMapper` | Maps provider language codes to domain enum |

Configuration: `STT_PROVIDER`, `STT_FASTER_WHISPER_BINARY`, `STT_FASTER_WHISPER_MODEL`.

---

# P32-SLICE-03 — Worker Integration

| Component | Role |
| --------- | ---- |
| `ProcessVideoHandler` | Application orchestration: STT → persist → artifact → status |
| `ProcessVideoMessageHandler` | Infrastructure messenger adapter |
| `DoctrineTranscriptRepository` | Persists transcript JSON per video |
| `TranscriptJsonMapper` | Application-layer JSON serialization (architecture-compliant) |
| `GetVideoTranscriptHandler` | Query handler for transcript retrieval |
| `GetVideoTranscriptController` | `GET /api/videos/{videoId}/transcript` |

Migration: `Version20260630120000` — `video_transcript` table.

Test env uses `FixedFasterWhisperProcessRunner` for deterministic functional tests.

---

# P32-SLICE-04 — Frontend Transcript Viewer

| Component | Role |
| --------- | ---- |
| `TranscriptService` | Validates UUID, delegates to repository |
| `HttpTranscriptRepository` | GET `/api/videos/{videoId}/transcript` |
| `MockTranscriptRepository` | Mock mode support |
| `TranscriptPanel` | Loads transcript by route param, manages active segment |
| `TranscriptTimeline` | Read-only segments, timestamps, highlight, scroll |
| `VideoTranscriptPage` | Route `/video/:videoId/transcript` |

Feature components use `transcriptService` only — no direct HTTP in features.

---

# P32-SLICE-05 — OpenAPI & Documentation

| Item | Location |
| ---- | -------- |
| `Transcript` schema | `Presentation/OpenApi/Schema/TranscriptSchema.php` |
| `TranscriptSegment` schema | `Presentation/OpenApi/Schema/TranscriptSegmentSchema.php` |
| `TranscriptLanguage` schema | `Presentation/OpenApi/Schema/TranscriptLanguageSchema.php` |
| Controller annotations | `GetVideoTranscriptController` |
| OpenAPI tests | 4 new tests in `ApiDocumentationTest` |
| Architecture rules | Speech-to-text foundation section |
| OpenAPI guide | `GET /api/videos/{videoId}/transcript` documented |

---

# Architectural decisions

| Decision | Rationale |
| -------- | --------- |
| `TranscriptJsonMapper` in Application layer | Architecture test forbids Application importing Infrastructure mappers |
| `videoId` as `ContentId` for artifacts | Reuses existing artifact pipeline without new FK constraints |
| `FixedFasterWhisperProcessRunner` in test env | Deterministic STT output for functional tests without CLI dependency |
| `scrollIntoView` guard in `TranscriptTimeline` | jsdom test environment lacks native scroll API |
| Provider factory via env | Enables future multi-provider sprint (Sprint 33+) without handler changes |

---

# Validation commands

```bash
docker compose exec backend php bin/phpunit
docker compose exec backend php bin/phpunit tests/Architecture
docker compose exec backend php bin/phpunit tests/Functional/OpenApi

docker compose exec frontend npm run build
docker compose exec frontend npm test
docker compose exec frontend npm run check

docker compose exec worker pytest
docker compose exec worker ruff check .
```

All commands passed on 2026-06-26.

---

# Documentation tree

```text
docs/
├── architecture/
│   ├── README.md              (Platform Sprint 32 section added)
│   ├── architecture-rules.md  (Speech-to-text foundation)
│   └── openapi.md             (GET /api/videos/{videoId}/transcript)
└── reports/
    ├── Sprint31-Verification.md
    └── Sprint32-Verification.md
```

---

# Platform capabilities after Sprint 32

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
| **Video Transcription** | ✅ |
| **Transcript Viewer** | ✅ |
| Video Translation | ⏳ Sprint 33 |

Sprint 33 can focus exclusively on **multilingual translation** via a provider system (Qwen, DeepSeek, Gemini, GPT) without revisiting transcription foundations.

---

# Sprint commits

| # | Hash | Message |
|---|------|---------|
| 1 | `2c6d46b` | feat(stt): add speech-to-text domain |
| 2 | `6d73470` | feat(stt): integrate faster-whisper provider |
| 3 | `7062328` | feat(worker): generate transcript artifacts |
| 4 | `b80e61c` | feat(frontend): add transcript viewer |
| 5 | `a1b1390` | docs(stt): document speech-to-text foundation |

**Additional fix during slice 03:** `bdb6292` — fix(stt): move TranscriptJsonMapper to application layer (architecture compliance).
