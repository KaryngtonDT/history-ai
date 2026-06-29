# Platform Sprint 31 — Final Verification Report

Version: 1.0

Status: Accepted

Date: 2026-06-26

---

# Executive summary

Platform Sprint 31 delivers the **Video Processing Foundation** for Phase 2 (AI Video Localization Platform). Users can upload mp4/mov/mkv videos via the frontend, the backend stores files locally, persists `VideoJob` records, and dispatches `ProcessVideoMessage` to a noop handler. Slice 5 changed **OpenAPI documentation, architecture docs, and verification only** — no business logic in backend handlers, frontend, or worker.

| Area | Result |
| ---- | ------ |
| Backend PHPUnit | ✅ 1034 tests, 3511 assertions |
| Backend architecture | ✅ 36 tests, 45 assertions |
| Backend OpenAPI | ✅ 64 tests, 713 assertions |
| Frontend build | ✅ OK |
| Frontend Vitest | ✅ 543 tests (105 files) |
| Frontend Biome | ✅ clean (502 files) |
| Worker pytest | ✅ 127 tests |
| Worker Ruff | ✅ All checks passed |
| VideoJob domain | ✅ Immutable lifecycle |
| Upload API | ✅ `POST /api/videos` |
| Storage & queue | ✅ Local disk + Messenger |
| Frontend upload UI | ✅ `VideoUploadPanel` |
| OpenAPI video schemas | ✅ `UploadVideoResponse`, `VideoStatus` |

---

# Platform Sprint 31 scope (slices 01–05)

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| P31-SLICE-01 | `VideoJob`, `VideoId`, `VideoStatus`, `VideoLanguage`, `VideoJobCollection` | ✅ |
| P31-SLICE-02 | `POST /api/videos` multipart upload endpoint | ✅ |
| P31-SLICE-03 | `LocalVideoStorage`, `DoctrineVideoRepository`, `ProcessVideoMessage` queue | ✅ |
| P31-SLICE-04 | `VideoUploadPanel`, `VideoService`, upload progress | ✅ |
| P31-SLICE-05 | OpenAPI video schemas, architecture docs, this report | ✅ |

---

# Final architecture

```text
User selects video file
        │
        ▼
VideoUploadPanel (frontend)
        │
        ├── validateVideo() — mp4, mov, mkv
        └── uploadVideo() + progress callback
        │
        ▼
POST /api/videos (multipart/form-data, field: video)
        │
        ▼
UploadVideoHandler
        ├── VideoExtension.fromFilename()
        ├── VideoUploadSize.assertWithinLimit()
        ├── LocalVideoStorage.store()
        ├── VideoJob.withStoragePath().queue()
        ├── DoctrineVideoRepository.save()
        └── MessengerVideoProcessingQueue.dispatch(ProcessVideoMessage)
        │
        ▼
HTTP 201 { videoId, status: "queued" }
        │
        ▼
ProcessVideoMessageHandler (noop — worker pipeline in Sprint 32+)
```

No AI processing, transcription, or worker execution yet. The queue message is dispatched synchronously in dev/test for contract stability.

---

# P31-SLICE-01 — VideoJob Domain

| Component | Role |
| --------- | ---- |
| `VideoId` | Immutable UUID value object |
| `VideoStatus` | Enum: uploaded, queued, processing, completed, failed |
| `VideoLanguage` | Enum: English, French, German, Unknown |
| `VideoJob` | Readonly aggregate with immutable transitions |
| `VideoJobCollection` | Ordered immutable collection |

Lifecycle: `Uploaded → Queued → Processing → Completed/Failed`

---

# P31-SLICE-02 — Upload API

| Item | Detail |
| ---- | ------ |
| Endpoint | `POST /api/videos` |
| Content-Type | `multipart/form-data` |
| Field | `video` |
| Formats | mp4, mov, mkv |
| Max size | `VIDEO_UPLOAD_MAX_BYTES` (default 500 MB) |
| Response 201 | `{ "videoId": "<uuid>", "status": "uploaded" }` → `"queued"` after slice 03 |

---

# P31-SLICE-03 — Storage & Queue

| Component | Role |
| --------- | ---- |
| `VideoRepositoryInterface` | Domain port for persistence |
| `LocalVideoStorage` | `{video.storage_dir}/{videoId}.{ext}` |
| `DoctrineVideoRepository` | Maps `VideoJob` ↔ `VideoJobRecord` |
| `ProcessVideoMessage` | Messenger message for future worker |
| `ProcessVideoMessageHandler` | No-op until processing pipeline |

Upload flow transitions job to `queued` before returning HTTP 201.

---

# P31-SLICE-04 — Frontend Upload

| Component | Role |
| --------- | ---- |
| `VideoService` | Client validation + upload orchestration |
| `HttpVideoRepository` | Multipart POST via `HttpClient.postFormData()` |
| `MockVideoRepository` | Simulated progress for mock mode |
| `VideoUploadPanel` | Phase machine: idle → uploading → success/error |
| `VideoDropzone` | Drag-and-drop + file picker |
| Route | `/video/upload` |

---

# P31-SLICE-05 — OpenAPI & Documentation

| Item | Location |
| ---- | -------- |
| `UploadVideoResponse` schema | `Presentation/OpenApi/Schema/UploadVideoResponseSchema.php` |
| `VideoStatus` schema | `Presentation/OpenApi/Schema/VideoStatusSchema.php` |
| Controller annotations | `UploadVideoController` |
| OpenAPI tests | `tests/Functional/OpenApi/ApiDocumentationTest.php` |
| Architecture index | `docs/architecture/README.md` |
| OpenAPI guide | `docs/architecture/openapi.md` |
| Architecture rules | `docs/architecture/architecture-rules.md` |
| Root README | Phase 2 video upload note |

---

# Validation commands

```bash
docker compose cp backend/src backend:/var/www/html/
docker compose cp backend/tests backend:/var/www/html/
docker compose cp backend/config backend:/var/www/html/

docker compose exec backend php bin/phpunit
docker compose exec backend php bin/phpunit tests/Architecture
docker compose exec backend php bin/phpunit tests/Functional/OpenApi

cd frontend && npm run build
cd frontend && npm test
cd frontend && npm run check

docker compose exec worker pytest
docker compose exec worker ruff check .
```

Host runs are acceptable when Docker sync is unavailable (`cd frontend; npm test`).

---

# Known limitations

| Topic | Current state |
| ----- | ------------- |
| Processing | `ProcessVideoMessageHandler` is a noop |
| Worker | No FFmpeg, Whisper, or transcription yet |
| Job listing | No GET endpoint for video jobs |
| Processing UI | Upload success only; no progress monitor |
| Language selection | `VideoLanguage` defaults to Unknown |
| Storage | Local filesystem only; no S3/cloud adapter |

---

# Future work (Sprint 32+)

| Item | Rationale |
| ---- | --------- |
| Faster-Whisper transcription | Sprint 32 — first AI capability |
| Translation providers | Sprint 33 — Qwen, DeepSeek, Gemini, GPT |
| TTS / voice cloning | Sprints 34–35 |
| Lip-sync & FFmpeg render | Sprints 36–37 |
| Transcript editor | Sprint 38 |
| Engine selection & auto mode | Sprints 39–40 |

---

# Documentation tree

```text
docs/
├── architecture/
│   ├── README.md              (Platform Sprint 31 section added)
│   ├── architecture-rules.md  (Video processing foundation)
│   └── openapi.md             (POST /api/videos, VideoStatus)
└── reports/
    ├── Sprint30-Verification.md
    └── Sprint31-Verification.md
```

---

# Platform capabilities after Sprint 31

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
| **Video Upload** | ✅ |
| **Video Job Persistence** | ✅ |
| **Video Queue Dispatch** | ✅ |
| Video Transcription | ⏳ Sprint 32 |
| Video Translation | ⏳ Sprint 33 |

Sprint 32 can wire the worker to consume `ProcessVideoMessage` and run Faster-Whisper transcription.
