# S3-SLICE-06 — Frontend Processing API Integration

Status: **Done**

Epic: **Epic 03 — Asynchronous Processing Pipeline**

Depends on: S3-SLICE-05

---

# Goal

Connect Processing page to real backend via `GET /api/processing-jobs/{id}`.

```text
ProcessingPage
  ↓
ProcessingService
  ↓
HttpProcessingRepository (VITE_USE_MOCK=false)
  ↓
GET /api/processing-jobs/{id}
```

---

# Created / updated

```text
frontend/src/services/processing/
  ProcessingRepository.ts          (async)
  HttpProcessingRepository.ts
  MockProcessingRepository.ts
  ProcessingRepositoryFactory.ts
  ProcessingService.ts
  types.ts                         (+ API mapping)
  *.test.ts

frontend/src/config/api.ts         (+ PROCESSING_JOBS_PATH)
frontend/src/features/processing/Processing/Processing.tsx
```

---

# Behaviour

| Mode | Behaviour |
| ---- | --------- |
| `VITE_USE_MOCK=true` | Mock data + client simulation (tests, local) |
| `VITE_USE_MOCK=false` | Single fetch from API, no simulation |

UI states: loading (Spinner), 404 (EmptyState), network error, data view.

---

# Out of scope

- Polling
- Worker
- Backend changes
- React Query / Zustand

---

# Next

Sprint 3 Worker slice or Sprint 4 Artifact domain
