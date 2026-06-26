# S2-SLICE-03 — Wire Library to Real Backend

Status: **Done**

Epic: **Epic 02 — Real Content Flow**

---

# Goal

Library loads from `GET /api/contents` via `HttpContentRepository`. UI components unchanged.

---

# Data flow

```text
Library
  ↓
ContentService.listContents()
  ↓
HttpContentRepository (VITE_USE_MOCK=false in Docker)
  ↓
HttpClient → GET http://localhost:8000/api/contents
  ↓
Symfony + PostgreSQL
```

---

# Configuration

| Context | VITE_USE_MOCK | Source |
|---------|---------------|--------|
| Docker frontend | `false` | `docker-compose.yml` build args |
| Vitest | `true` | `vite.config.ts` |
| Local dev (mock) | `true` | `frontend/.env` |

---

# CORS

`CorsSubscriber` (dev only) allows `http://localhost:5173` on `/api/*`.

---

# Error handling

Network/API errors → existing `EmptyState` ("Unable to load library").

Empty array → existing `EmptyState` ("No content yet").

---

# Next

**S2-04** — Wire Import to `POST /api/contents`
