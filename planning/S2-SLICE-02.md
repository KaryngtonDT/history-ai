# S2-SLICE-02 — HttpContentRepository

Status: **Done**

Epic: **Epic 02 — Real Content Flow**

---

# Goal

Connect frontend to Symfony API by swapping the repository adapter only. UI unchanged.

---

# Architecture

```text
Dashboard / Library / Import
        ↓
   ContentService
        ↓
ContentRepositoryFactory
        ↓
   VITE_USE_MOCK=true  → MockContentRepository
   VITE_USE_MOCK=false → HttpContentRepository → HttpClient → Symfony
```

---

# New files

```text
frontend/src/config/api.ts
frontend/src/services/http/HttpClient.ts
frontend/src/services/content/ContentRepositoryFactory.ts
```

---

# Rules

- `fetch` only in `HttpClient`
- No hardcoded URLs in repositories (`config/api.ts`)
- Factory owns repository selection
- Sync UI preserved via `VITE_USE_MOCK=true` (default)
- `ContentRepository` is async-only — no sync/async bridge

---

# Next

**S2-03** — Wire Import to `createContentAsync()` with HTTP
