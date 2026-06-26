# S2-02 — HttpContentRepository

Status: **Done**

Epic: **Epic 02 — Real Content Flow**

---

# Goal

Implement the HTTP adapter for Symfony Content API. Mock remains the active repository until S2-03.

---

# API mapping

| Frontend | Backend |
|----------|---------|
| `pdf` | `upload_pdf` |
| `audio` | `upload_audio` |
| `video` | `upload_video` |
| `youtube` | `youtube_url` |

| Backend status | UI status | progress |
|----------------|-----------|----------|
| `draft`, `imported`, `processing`, `failed` | `processing` | 0 |
| `completed` | `completed` | 100 |

---

# Files

```text
services/content/
  HttpContentRepository.ts      ← fetch GET/POST /api/contents
  HttpContentRepository.test.ts
  mapContentFromApi.ts
  mapSourceType.ts
  apiTypes.ts
  ContentApiError.ts
```

---

# Dev proxy

`vite.config.ts` proxies `/api` → `http://localhost:8000` in dev mode.

Override with `VITE_API_BASE_URL` when needed.

---

# Next

**S2-03** — Wire Import to `HttpContentRepository.createContent()`
