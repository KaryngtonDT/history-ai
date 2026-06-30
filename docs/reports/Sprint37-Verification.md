# Platform Sprint 37 — Final Verification Report

Version: 1.0

Status: Accepted

Date: 2026-06-26

Commit: _(set after final push)_

---

# Executive summary

Platform Sprint 37 delivers **Lip Sync Foundation** for Phase 2 (AI Video Localization Platform). Users can synchronize lip movements with cloned translated audio using LatentSync, preview before/after video, and persist lip-sync artifacts. Wav2Lip remains registered but disabled.

| Area | Result |
| ---- | ------ |
| Backend PHPUnit | ✅ 1225 tests |
| Backend architecture | ✅ 36 tests |
| Backend OpenAPI | ✅ 92 tests |
| Frontend build | ✅ OK |
| Frontend Vitest | ✅ 570 tests |
| Frontend Biome | ✅ clean |
| Worker pytest | ✅ |
| Worker Ruff | ✅ |
| Lip sync domain | ✅ Pure domain + `LipSyncProviderInterface` |
| LatentSync provider | ✅ Enabled via AI Engine; Wav2Lip disabled |
| Lip sync worker | ✅ `VideoLipSyncGenerator` + artifact persistence |
| Frontend lip sync | ✅ `LipSyncPreview` + compare mode at `/video/:videoId/lip-sync` |
| OpenAPI lip sync schemas | ✅ `LipSyncArtifact`, `LipSyncProvider` |

---

# Platform Sprint 37 scope (slices 01–05)

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| P37-SLICE-01 | `LipSyncArtifact`, `LipSyncProviderInterface` | ✅ |
| P37-SLICE-02 | `LatentSyncProvider`, factory, AI Engine integration | ✅ |
| P37-SLICE-03 | `VideoLipSyncGenerator`, lip sync artifacts, REST endpoints | ✅ |
| P37-SLICE-04 | `LipSyncPreview`, `LipSyncSettings`, `LipSyncService` | ✅ |
| P37-SLICE-05 | OpenAPI lip sync schemas, architecture docs, this report | ✅ |

---

# Final architecture

```text
Video Upload (POST /api/videos)
        │
        ▼
ProcessVideoHandler
        ├── Transcript → Translation
        ├── (GENERATE_AUDIO=true) VideoAudioGenerator → F5-TTS
        ├── (GENERATE_VOICE_CLONE=true) VideoVoiceCloneGenerator → OpenVoice
        └── (GENERATE_LIP_SYNC=true) VideoLipSyncGenerator → LatentSync
        │
        ▼
GET/POST /api/videos/{videoId}/lip-sync → LipSyncPanel
```

---

# P37-SLICE-01 — Lip Sync Domain

| Component | Role |
| --------- | ---- |
| `LipSyncArtifact` | Immutable: artifactId, sourceVideoId, clonedAudioId, provider, synced video |
| `LipSyncVideo` | Immutable: synchronizedVideoId, storagePath, duration |
| `LipSyncProvider` | Enum: LatentSync, Wav2Lip, Mock |
| `LipSyncProviderInterface` | Domain port: `synchronize(VideoJob, VoiceCloneArtifact)` |
| `AIEngineCapability::LipSync` | Capability in AI Engine registry (from Sprint 34) |

---

# P37-SLICE-02 — LatentSync Provider

| Component | Role |
| --------- | ---- |
| `LatentSyncProvider` | Infrastructure implementation of lip sync port |
| `FixedLatentSyncProcessRunner` | Test/dev process runner |
| `LipSyncMapper` | Maps process output to `LipSyncArtifact` |
| `LipSyncProviderFactory` | Creates provider by configuration |
| `AIProviderResolver::resolveLipSync()` | Capability resolution via AI Engine |

Registry after Sprint 37:

| Capability | Provider | Status |
| ---------- | -------- | ------ |
| LipSync | LatentSync | ✅ enabled |
| LipSync | Wav2Lip | ⏳ disabled |

Configuration: `LIP_SYNC_PROVIDER=latentsync`, `LATENTSYNC_MODEL=latentsync`, `LATENTSYNC_PATH=/models/latentsync`

---

# P37-SLICE-03 — Lip Sync Worker

| Component | Role |
| --------- | ---- |
| `VideoLipSyncGenerator` | Voice clone + original video → LatentSync → persistence |
| `DoctrineLipSyncRepository` | Persists lip sync metadata per target language |
| `ProcessVideoHandler` | Calls generator when `GENERATE_LIP_SYNC=true` |

REST endpoints:

| Method | Path |
| ------ | ---- |
| GET | `/api/videos/{videoId}/lip-sync` |
| POST | `/api/videos/{videoId}/lip-sync` |
| GET | `/api/videos/{videoId}/lip-sync/{language}` |
| GET | `/api/videos/{videoId}/lip-sync/{language}/stream` |

---

# P37-SLICE-04 — Frontend Lip Sync Preview

| Component | Role |
| --------- | ---- |
| `LipSyncService` | Validates and delegates to repository |
| `LipSyncSettings` | Provider selection, language checkboxes, generate |
| `LipSyncPreview` | Embedded video player, before/after comparison, replay |
| `VideoLipSyncPage` | Route `/video/:videoId/lip-sync` |

Feature components use `lipSyncService` only — no direct HTTP in features.

---

# P37-SLICE-05 — OpenAPI & Documentation

| Item | Location |
| ---- | -------- |
| `LipSyncProvider` schema | `Presentation/OpenApi/Schema/LipSyncProviderSchema.php` |
| `LipSyncArtifact` schema | `Presentation/OpenApi/Schema/LipSyncArtifactSchema.php` |
| Controller annotations | List/Get/Generate lip sync controllers |
| OpenAPI tests | 4 new tests in `ApiDocumentationTest` |
| Architecture docs | `docs/architecture/README.md`, `openapi.md`, `architecture-rules.md` |

---

# Architectural decisions

| Decision | Rationale |
| -------- | --------- |
| Lip sync as dedicated capability | Distinct from TTS and voice clone; own provider interface |
| LatentSync primary, Wav2Lip disabled | Same pattern as OpenVoice/SeedVC and F5-TTS/Kokoro |
| `GENERATE_LIP_SYNC=false` by default | Requires voice clone first; opt-in in worker |
| Preview only (no final render) | Sprint 38 will add FFmpeg final MP4 export |
| Before/after video comparison | UX parity with voice clone compare mode |

---

# Functional criteria

| Criterion | Status |
| --------- | ------ |
| User can generate lip-synced video preview | ✅ |
| LatentSync is active engine | ✅ |
| Wav2Lip ready via same capability | ✅ |
| Lip-synced videos persisted as artifacts | ✅ |
| Video preview with comparison available | ✅ |
| Integration via AI Engine Platform | ✅ |

---

# Validation commands

```bash
docker compose build backend && docker compose up -d backend
docker compose exec backend php bin/phpunit
docker compose exec backend php bin/phpunit tests/Architecture
docker compose exec backend php bin/phpunit tests/Functional/OpenApi

cd frontend && npm run build && npm test && npm run check

docker compose exec worker pytest
docker compose exec worker ruff check .
```

---

# Sprint commits

| Slice | Commit | Message |
| ----- | ------ | ------- |
| P37-SLICE-01 | `678af3d` | feat(lipsync): add lip sync domain |
| P37-SLICE-02 | `905a30a` | feat(lipsync): integrate latentsync provider |
| P37-SLICE-03 | `2d4761a` | feat(worker): generate lip synced videos |
| P37-SLICE-04 | `f1a351e` | feat(frontend): add lip sync preview |
| P37-SLICE-05 | _(this commit)_ | docs(lipsync): document lip sync foundation |

---

# Next sprint

| Feature | Sprint |
| ------- | ------ |
| Final MP4 rendering + download | Sprint 38 |
