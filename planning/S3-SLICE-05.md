# S3-SLICE-05 — Wire Processing Page to Real API

Status: **Planned**

Epic: **Epic 03 — Asynchronous Processing Pipeline**

Depends on: S3-SLICE-04

---

# Goal

Replace `MockProcessingRepository` and client-side `simulateProcessing()` with real HTTP polling.

---

# Frontend structure (mirror Content)

Apply S2-06 conventions:

```text
services/processing/
  api/ProcessingApiDto.ts
  domain/Processing.ts
  mappers/ProcessingMapper.ts
  ProcessingRepository.ts
  HttpProcessingRepository.ts
  ProcessingRepositoryFactory.ts   ← FEATURES.USE_MOCK
  ProcessingService.ts
```

Remove or deprecate:

- `services/processing/types.ts` → `domain/Processing.ts`
- Client-side `SIMULATION_FRAMES` in `ProcessingService`

---

# Data flow

```text
ProcessingPage
  ↓
ProcessingService.startProcessing(contentId)   → POST /contents/{id}/process
ProcessingService.pollStatus(jobId)            → GET /processing/{id}
  ↓
HttpProcessingRepository
  ↓
HttpClient
```

Polling interval: ~2s (match Worker tick). Stop on `completed` or `failed`.

---

# Import integration (minimal)

After successful Import (`POST /contents`), navigate to Processing with new content id and trigger `POST /process`.

No UI layout changes — wire existing flow only.

---

# Error handling

Use shared errors (`ApiError`, `NetworkError`). Processing page shows existing error/empty patterns.

---

# Tests

| Layer | Coverage target |
| ----- | --------------- |
| ProcessingMapper | 100% |
| HttpProcessingRepository | ≥ 90% |
| ProcessingService | ≥ 90% |
| ProcessingRepositoryFactory | env toggle |

---

# Out of scope

- UI redesign
- Worker (S3-06)
- Removing mock entirely until Docker path verified

---

# Acceptance criteria

- [ ] `HttpProcessingRepository` implements `ProcessingRepository`
- [ ] Processing page loads real status from API
- [ ] Mock removed from production Docker build path
- [ ] No `fetch` outside `HttpClient`
- [ ] No API DTO types in feature components
- [ ] All tests + Biome green

---

# Next

**S3-SLICE-06** — Worker (simulated lifecycle)
