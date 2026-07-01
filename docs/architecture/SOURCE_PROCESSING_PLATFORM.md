# Source Processing Platform

Version: 1.0  
Date: 2026-07-02  
Status: Accepted (Sprint 51 — Audio foundation)

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

Sprint 51 implements the **domain and first connector: Audio Upload**.  
Sprint 52 will add **YouTube** as another connector on the same platform.

---

## Domain (`backend/src/Domain/Source/`)

| Type | Role |
|------|------|
| `Source` | Aggregate — lifecycle like `VideoJob` but type-agnostic |
| `SourceType` | `video`, `audio`, `pdf`, `youtube` |
| `SourceStatus` | `uploaded` → `queued` → `processing` → `completed` / `failed` |
| `SourceProcessorInterface` | Connector contract for future sources |
| `SourceRepositoryInterface` | Persistence port |

**Video and Content domains are unchanged.** Audio uses `Source` + shared artifact keys (UUID).

---

## Audio API

| Method | Path | Role |
|--------|------|------|
| `POST` | `/api/audio` | Upload audio file |
| `GET` | `/api/audio` | List recent audio sources |
| `GET` | `/api/audio/{audioId}` | Source metadata |
| `DELETE` | `/api/audio/{audioId}` | Delete source + file |

Supported formats: **mp3, wav, flac, m4a, ogg**.

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

## Frontend

| Route | Role |
|-------|------|
| `/audio/upload` | Create audio source |
| `/audio/:audioId` | Overview hub |
| `/audio/:audioId/transcript` | Transcript detail |
| `/audio/:audioId/translations` | Translation detail |

Home **Create → Audio** links to `/audio/upload`.  
YouTube card shows **Coming soon** (Sprint 52).

WorkItem `audio` type routes to `/audio/:id`.

---

## Public API impact (Sprint 53+)

Recommended resources:

- `POST /api/audio` (implemented)
- `POST /api/youtube` (Sprint 52)
- `GET /api/work-items` (product read model)

---

## Related

- [PRODUCT_INFORMATION_ARCHITECTURE.md](./PRODUCT_INFORMATION_ARCHITECTURE.md)
- [Sprint51-Verification.md](../reports/Sprint51-Verification.md)
