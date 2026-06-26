# Tech debt — ContentSourceType alignment

Status: **Open** (fix after Sprint 2 vertical slice)

---

# Problem

Frontend and backend use **different strings** for the same concept:

| Layer | Example values |
|-------|----------------|
| **Backend / API** | `upload_pdf`, `upload_audio`, `upload_video`, `youtube_url` |
| **Frontend domain (today)** | `pdf`, `audio`, `video`, `youtube` |

Mapping lives only in `frontend/src/services/content/mapSourceType.ts`. UI labels and mocks use the short form.

This works for S2-03/S2-04 but drifts from **one shared language** (DDD end-to-end).

---

# Target state

Use **backend enum values everywhere** in the frontend domain model:

```ts
export type ContentSourceType =
  | "upload_pdf"
  | "upload_audio"
  | "upload_video"
  | "youtube_url";
```

- `HttpContentRepository` — pass through, no map on write/read
- `MockContentRepository` / `mock/content.ts` — same values
- UI labels — derive display text from enum (`upload_pdf` → "PDF")
- Remove or shrink `mapSourceType.ts`

---

# Scope when fixing

- [ ] `types.ts`, mocks, tests
- [ ] `LibraryContentCard` source labels
- [ ] `ContentService.importPdf()` → `sourceType: "upload_pdf"`
- [ ] Delete redundant mappers if API = domain

---

# Reference

Backend: `App\Domain\Content\ContentSourceType`

Created during S2-SLICE-04 review.
