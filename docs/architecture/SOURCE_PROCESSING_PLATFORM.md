# Source Processing Platform

Version: 1.1  
Date: 2026-07-13  
Status: Accepted (Sprint 52 — YouTube connector)

---

## Vision

History AI ingests multiple **source types** through one platform layer:

```text
Source (Video | Audio | PDF | YouTube | …)
        ↓
   Ingestion
        ↓
 Normalization
        ↓
    Pipeline
        ↓
   Artifacts
```

Sprint 51 implemented the **domain and first connector: Audio Upload**.  
Sprint 52 adds **YouTube** as a connector that feeds the **existing video pipeline**.

---

## Domain (`backend/src/Domain/Source/`)

| Type | Role |
|------|------|
| `Source` | Aggregate — lifecycle like `VideoJob` but type-agnostic |
| `SourceType` | `video`, `audio`, `pdf`, `youtube` |
| `SourceStatus` | `uploaded` → `queued` → `processing` → `completed` / `failed` |
| `SourceProcessorInterface` | Connector contract for future sources |
| `SourceRepositoryInterface` | Persistence port |

**Video and Content domains are unchanged.** Connectors use `Source` + shared artifact keys (UUID).

---

## YouTube domain (`backend/src/Domain/YouTube/`)

| Type | Role |
|------|------|
| `YouTubeVideo` | Aggregate linking import metadata to `VideoJob` |
| `YouTubeVideoId` | UUID (= `SourceId`) |
| `YouTubeUrl` | Validated YouTube URL value object |
| `YouTubeMetadata` | Title, duration, thumbnail, language |
| `YouTubeImportRequest` | URL + processing options |
| `YouTubeImportResult` | Import outcome with `videoId` |
| `YouTubeImporterInterface` | Download port (yt-dlp in prod, mock in tests) |

YouTube does **not** introduce `YouTubeTranscript`, `YouTubeTranslation`, etc. All artifacts use the video pipeline keyed by `videoId`.

---

## Audio API (Sprint 51)

| Method | Path | Role |
|--------|------|------|
| `POST` | `/api/audio` | Upload audio file |
| `GET` | `/api/audio` | List recent audio sources |
| `GET` | `/api/audio/{audioId}` | Source metadata |
| `DELETE` | `/api/audio/{audioId}` | Delete source + file |

Supported formats: **mp3, wav, flac, m4a, ogg**.

---

## YouTube API (Sprint 52)

| Method | Path | Role |
|--------|------|------|
| `POST` | `/api/youtube` | Import URL → `VideoJob` + queue pipeline |
| `POST` | `/api/youtube/preview` | Metadata preview (no download) |
| `GET` | `/api/youtube` | List recent imports |
| `GET` | `/api/youtube/{youtubeId}` | Import detail |

Import flow:

```text
POST /api/youtube { url }
    → validate YouTubeUrl
    → YouTubeImporterInterface::download()
    → store file via VideoStorageInterface
    → create VideoJob + Source (type: youtube)
    → VideoProcessingQueueInterface::enqueue()
```

---

## Audio pipeline (Sprint 51)

```text
Upload → STT (transcribePath) → Translation → Artifacts
```

Reuses:

- `SpeechToTextProviderInterface::transcribePath()`
- `VideoTranslationGenerator` (artifact key = audio UUID)
- Messenger sync queue (`ProcessAudioMessage`)

Does **not** run: Lip Sync, Video Render, Voice Clone.

---

## YouTube pipeline (Sprint 52)

```text
YouTube URL → Download → VideoJob → Existing Video Pipeline
```

Reuses the **full video pipeline**:

- STT → Translation → TTS → Voice Clone → Lip Sync → Render → Quality

No YouTube-specific processing stages.

---

## Frontend

| Route | Role |
|-------|------|
| `/audio/upload` | Create audio source |
| `/audio/:audioId` | Audio overview hub |
| `/audio/:audioId/transcript` | Transcript detail |
| `/audio/:audioId/translations` | Translation detail |
| `/youtube/import` | Paste YouTube URL, preview, import |
| `/video/:videoId` | Video hub (upload **or** YouTube import) |

Home **Create** section:

- 🎥 Video → `/video/upload`
- 🎵 Audio → `/audio/upload`
- 📄 PDF → `/import`
- ▶️ YouTube → `/youtube/import`

WorkItem routing:

- `audio` → `/audio/:id`
- `youtube` → `/video/:videoId` (same as video)

---

## Public API impact (Sprint 53+)

Recommended resources:

- `POST /api/audio` (implemented)
- `POST /api/youtube` (implemented)
- `GET /api/work-items` (product read model)

---

## Related

- [PRODUCT_INFORMATION_ARCHITECTURE.md](./PRODUCT_INFORMATION_ARCHITECTURE.md)
- [Sprint51-Verification.md](../reports/Sprint51-Verification.md)
- [Sprint52-Verification.md](../reports/Sprint52-Verification.md)
