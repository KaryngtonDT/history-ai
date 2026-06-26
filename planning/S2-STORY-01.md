# S2-01 — Unify Frontend Around Content Domain

Status: **Done**

Epic: **Epic 02 — Real Content Flow**

---

# Goal

Align frontend services with backend domain model: one `Content` aggregate, one `ContentService`.

---

# Data flow

```text
Dashboard / Library / Import
    ↓
ContentService
    ↓
ContentRepository
    ↓
MockContentRepository  (default — S2-01)
HttpContentRepository  (implemented — wired in S2-03+)
    ↓
mock/content.ts
```

---

# Structure

```text
frontend/src/services/content/
  ContentRepository.ts
  MockContentRepository.ts
  HttpContentRepository.ts
  ContentService.ts
  computeStatistics.ts
  types.ts
```

---

# Acceptance criteria

- [x] `ContentRepository` interface
- [x] `MockContentRepository`
- [x] `HttpContentRepository` (real fetch — see S2-STORY-02.md)
- [x] `ContentService` exposes `listContents()`, `createContent()`, `getDashboardData()`
- [x] Dashboard, Library, Import use `contentService`
- [x] No React component calls a repository directly
- [x] Mocks continue to work

---

# Removed (screen-based services)

- `services/dashboard/`
- `services/library/`
- `services/import/`
- `mock/dashboard.ts`
- `mock/library.ts`

---

# Next

**S2-02** — Implement `HttpContentRepository` (done)

**S2-03** — Wire Import to POST /api/contents
