# Platform Sprint 35 — Final Verification Report

Version: 1.0

Status: Accepted

Date: 2026-06-26

---

# Executive summary

Platform Sprint 35 delivers **Text-to-Speech Foundation** for Phase 2 (AI Video Localization Platform). Users can generate translated audio from existing translations using F5-TTS (primary engine), preview playback in the browser, and download WAV files. Kokoro and XTTS remain registered but disabled for future slices.

| Area | Result |
| ---- | ------ |
| Backend PHPUnit | ✅ 1170 tests, 3925 assertions |
| Backend architecture | ✅ 36 tests, 45 assertions |
| Backend OpenAPI | ✅ 84 tests, 833 assertions |
| Frontend build | ✅ OK |
| Frontend Vitest | ✅ 563 tests (115 files) |
| Frontend Biome | ✅ clean (569 files) |
| Worker pytest | ✅ 127 tests |
| Worker Ruff | ✅ All checks passed |
| TTS domain | ✅ Immutable `AudioArtifact`, `Voice`, provider interface |
| F5-TTS provider | ✅ Enabled via AI Engine; Kokoro/XTTS disabled |
| Audio worker | ✅ `VideoAudioGenerator` + artifact persistence |
| Frontend audio preview | ✅ `AudioPlayerPanel` + `VoiceSelector` at `/video/:videoId/audio` |
| OpenAPI audio schemas | ✅ `AudioArtifact`, `Voice`, TTS enums |

---

# Platform Sprint 35 scope (slices 01–05)

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| P35-SLICE-01 | `AudioArtifact`, `Voice`, `TextToSpeechProviderInterface` | ✅ |
| P35-SLICE-02 | `F5TextToSpeechProvider`, factory, AI Engine integration | ✅ |
| P35-SLICE-03 | `VideoAudioGenerator`, audio artifacts, REST endpoints | ✅ |
| P35-SLICE-04 | `AudioPlayerPanel`, `VoiceSelector`, `AudioService` | ✅ |
| P35-SLICE-05 | OpenAPI audio schemas, architecture docs, this report | ✅ |

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
        ├── VideoTranslationGenerator
        │       └── AIProviderResolver.resolveTranslation() → Ollama
        └── (when GENERATE_AUDIO=true) VideoAudioGenerator
                ├── AIProviderResolver.resolveTextToSpeech() → F5-TTS
                ├── AudioRepository.save() × N languages
                └── ArtifactRepository.create(ArtifactType::Audio) × N
        │
        ▼
GET/POST /api/videos/{videoId}/audio → AudioPlayerPanel (/video/:videoId/audio)
```

No voice cloning, lip-sync, or video rendering in this sprint.

---

# P35-SLICE-01 — Text-to-Speech Domain

| Component | Role |
| --------- | ---- |
| `AudioArtifact` | Immutable aggregate: id, translationId, provider, voice, duration, format, storage path |
| `AudioId` | Value object identifying an audio artifact |
| `Voice` | Immutable: voiceId, displayName, language, gender |
| `VoiceCollection` | Collection of available voices |
| `VoiceGender` | Enum: Male, Female, Neutral |
| `VoiceLanguage` | Enum: English, French, German, Spanish, Italian |
| `TextToSpeechProvider` | Enum: F5TTS, Kokoro, XTTS, Mock |
| `TextToSpeechProviderInterface` | Domain port: `synthesize(Translation, Voice): AudioArtifact` |
| `InvalidAudioArtifactException` | Domain validation errors |

---

# P35-SLICE-02 — F5-TTS Provider

| Component | Role |
| --------- | ---- |
| `F5TextToSpeechProvider` | Infrastructure implementation of TTS port |
| `ShellF5ProcessRunner` / `FixedF5ProcessRunner` | Process execution (fixed runner in tests) |
| `AudioMapper` | Maps process output to `AudioArtifact` |
| `TextToSpeechProviderFactory` | Creates provider by configuration |
| `MockTextToSpeechProvider` | Test/dev mock provider |
| `AIProviderResolver::resolveTextToSpeech()` | Capability resolution via AI Engine |

Registry after Sprint 35:

| Capability | Provider | Status |
| ---------- | -------- | ------ |
| SpeechToText | FasterWhisper | ✅ enabled |
| Translation | Ollama | ✅ enabled |
| TextToSpeech | F5-TTS | ✅ enabled |
| TextToSpeech | Kokoro, XTTS | ⏳ disabled |
| VoiceClone | OpenVoice, SeedVC | ⏳ disabled |
| LipSync | LatentSync, Wav2Lip | ⏳ disabled |

Configuration: `TTS_PROVIDER=f5`, `F5_MODEL=F5-TTS`, `F5_BASE_PATH=/models/f5`

---

# P35-SLICE-03 — Audio Worker

| Component | Role |
| --------- | ---- |
| `VideoAudioGenerator` | Orchestrates translation → TTS → persistence |
| `DefaultVoiceSelector` | Selects default voice per language from `VoiceCatalog` |
| `DoctrineAudioRepository` | Persists audio metadata |
| `AudioRecord` + migration | Database schema for audio artifacts |
| `ProcessVideoHandler` | Calls `VideoAudioGenerator` when `GENERATE_AUDIO=true` |
| `GenerateVideoAudioHandler` | On-demand audio generation via POST |
| `StreamVideoAudioHandler` | Serves WAV via `BinaryFileResponse` |

REST endpoints:

| Method | Path |
| ------ | ---- |
| GET | `/api/videos/{videoId}/audio` |
| POST | `/api/videos/{videoId}/audio` |
| GET | `/api/videos/{videoId}/audio/{language}` |
| GET | `/api/videos/{videoId}/audio/{language}/stream` |

---

# P35-SLICE-04 — Frontend Audio Preview

| Component | Role |
| --------- | ---- |
| `AudioService` | Validates and delegates to repository |
| `HttpAudioRepository` | GET/POST audio endpoints |
| `MockAudioRepository` | Mock mode support |
| `AudioPlayerPanel` | Play/pause, duration, download, provider display |
| `VoiceSelector` | Provider and voice selection |
| `VideoAudioPage` | Route `/video/:videoId/audio` |

Feature components use `audioService` only — no direct HTTP in features.

---

# P35-SLICE-05 — OpenAPI & Documentation

| Item | Location |
| ---- | -------- |
| `AudioArtifact` schema | `Presentation/OpenApi/Schema/AudioArtifactSchema.php` |
| `TextToSpeechProvider` schema | `Presentation/OpenApi/Schema/TextToSpeechProviderSchema.php` |
| `VoiceGender` / `VoiceLanguage` schemas | `Presentation/OpenApi/Schema/` |
| `VideoAudioList` / `VideoAudioSummary` schemas | `Presentation/OpenApi/Schema/` |
| `GenerateVideoAudioRequest` schema | `Presentation/OpenApi/Schema/GenerateVideoAudioRequestSchema.php` |
| Controller annotations | `ListVideoAudioController`, `GetVideoAudioController`, `GenerateVideoAudioController` |
| OpenAPI tests | 4 new tests in `ApiDocumentationTest` |
| Architecture rules | Text-to-Speech Foundation section |
| OpenAPI guide | Video audio endpoints section |

---

# Architectural decisions

| Decision | Rationale |
| -------- | --------- |
| F5-TTS as primary engine in Sprint 35 | Mature enough for reference quality; Kokoro added later via same capability |
| `VoiceCatalog` in Domain | Architecture test forbids Application importing Infrastructure types |
| `VideoAudioGenerator` not `final` | Enables mocking in `ProcessVideoHandlerTest` |
| Stream endpoint separate from metadata | Binary WAV delivery without JSON wrapper |
| `GENERATE_AUDIO=false` by default | Opt-in auto-generation in worker; manual POST always available |
| `ArtifactType::Audio` + `LibraryItemType::Audio` | Consistent artifact model with transcript/translation |

---

# Functional criteria

| Criterion | Status |
| --------- | ------ |
| A translation can generate audio | ✅ |
| Multiple languages produce multiple audio files | ✅ |
| User chooses voice | ✅ |
| User chooses TTS engine | ✅ |
| Audios persisted as artifacts | ✅ |
| Audio player enables preview before dubbing | ✅ |

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

# Documentation tree

```text
docs/
├── architecture/
│   ├── README.md              (Platform Sprint 35 section added)
│   ├── architecture-rules.md  (Text-to-Speech Foundation section)
│   └── openapi.md             (Video audio endpoints + schemas)
└── reports/
    ├── Sprint31-Verification.md
    ├── Sprint32-Verification.md
    ├── Sprint33-Verification.md
    ├── Sprint34-Verification.md
    └── Sprint35-Verification.md
```

---

# Platform capabilities after Sprint 35

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
| AI Engine Platform | ✅ |
| **Text-to-Speech** | ✅ |
| Voice Cloning | ⏳ Sprint 36 |
| Lip-sync | ⏳ Sprint 37 |
| Video Rendering | ⏳ Sprint 38 |

---

# Sprint 35 commits

| # | Hash | Message |
| - | ---- | ------- |
| 1 | `a97a6b9` | feat(tts): add text-to-speech domain |
| 2 | `8517647` | feat(tts): integrate f5-tts provider |
| 3 | `6aa9fbe` | feat(worker): generate audio artifacts |
| 4 | `e95fc29` | feat(frontend): add audio preview |
| 5 | `190765d` | docs(tts): document text-to-speech foundation |
