# Sprint 52 — YouTube Processing Platform

**Date:** 2026-07-13  
**Status:** Complete

---

## Summary

Sprint 52 adds **YouTube as a Source connector** on the Source Processing Platform (Sprint 51). A pasted YouTube URL is downloaded, converted to a `VideoJob`, and processed through the **existing video pipeline** — no YouTube-specific transcript, translation, or render stages.

---

## Commits

| Slice | Message |
|-------|---------|
| 01 | `feat(youtube): add youtube source domain` |
| 02 | `feat(youtube): implement youtube import` |
| 03 | `feat(worker): integrate youtube processing` |
| 04 | `feat(frontend): add youtube processing experience` |
| 05 | `docs(youtube): document youtube processing platform` |

---

## Architecture

```text
YouTube URL
    ↓
YouTubeImporterInterface (yt-dlp in prod, mock in tests)
    ↓
VideoJob + Source (type: youtube)
    ↓
Existing video pipeline (STT → translation → audio → voice clone → lip sync → render → quality)
    ↓
/video/:videoId (same UX as uploaded video)
```

| Rule | Detail |
|------|--------|
| YouTube ID | `YouTubeVideoId` = `SourceId` (UUID) |
| No duplicate pipeline | Reuses `VideoProcessingQueueInterface` |
| Artifacts | Keyed by `videoId` — no `YouTubeTranscript` types |
| WorkItem | `youtube` type routes to `/video/:videoId` |

---

## Backend

- Domain: `backend/src/Domain/YouTube/`
- Application: `backend/src/Application/YouTube/`
- Infrastructure: `YtDlpYouTubeImporter`, `MockYouTubeImporter`, Doctrine repository
- API:
  - `POST /api/youtube` — import and queue processing
  - `POST /api/youtube/preview` — metadata preview (title, thumbnail, duration)
  - `GET /api/youtube` — list imports
  - `GET /api/youtube/{youtubeId}` — import detail
- Migration: `youtube_import` table (`Version20260713120000`)
- Docker: `yt-dlp` installed in backend image

---

## Frontend

| Route | Role |
|-------|------|
| `/youtube/import` | URL input, preview, AI mode, import |
| `/video/:videoId` | Post-import hub (same as upload) |

- `youtubeSourceService` repository pattern (Http / Mock)
- Home **Create → YouTube** — "Coming soon" badge removed
- WorkItem `youtube` → `/video/:videoId`

---

## Security

| Concern | Mitigation |
|---------|------------|
| Invalid URLs | `YouTubeUrl` value object + `InvalidYouTubeException` → 400 |
| Unsupported hosts | URL validation rejects non-YouTube links |
| Import failures | `YouTubeImporterException` → 502 with safe message |
| SSRF | Download only via validated YouTube video ID + yt-dlp |

---

## Validation

| Check | Result |
|-------|--------|
| PHPUnit (full suite, 1530 tests) | ✅ |
| PHPUnit YouTube functional tests | ✅ |
| `npm run build` | ✅ |
| `npm test` | ✅ |
| `npm run check` | ✅ |
| `pytest` / `ruff` | ✅ (worker container, 127 tests) |

---

## CTO checklist

- [x] YouTube is a `Source` (`SourceType::Youtube`)
- [x] No duplicated video pipeline
- [x] Full video pipeline reuse after import
- [x] Secure URL validation and import error handling
- [x] Automatic `VideoJob` creation
- [x] YouTube `WorkItem` integrated
- [x] Home updated (YouTube live)
- [x] OpenAPI documented
- [x] Test suites green

---

## Roadmap update

```text
50.5 Product IA
51   Source Platform (Audio)
52   YouTube connector          ← this sprint
53   Public API
54   Official SDKs
```

---

## Known limitations

1. Preview requires network access to YouTube (yt-dlp metadata fetch).
2. Very long videos may hit storage/time limits — same constraints as large uploads.
3. Workspace batch processing does not yet include YouTube imports explicitly.
