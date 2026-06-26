# S3-SLICE-06 — Processing Worker (Simulated)

Status: **Planned**

Epic: **Epic 03 — Asynchronous Processing Pipeline**

Depends on: S3-SLICE-02, S3-SLICE-04

---

# Goal

Introduce a background worker that advances ProcessingJob through its lifecycle — **no AI**, no file I/O.

---

# Behaviour

Every **2 seconds**:

```text
1. Find ProcessingJob where status = Pending
2. Transition → Running (progress 10, currentStep: "Starting")
3. After next tick → increment progress (+30 cap 90)
4. After final tick → Completed (progress 100, currentStep: "Completed")
```

Simple state machine — proves async pipeline end-to-end.

---

# Implementation options

Pick one during implementation (document in PR):

| Option | Pros | Cons |
| ------ | ---- | ---- |
| **A. Python worker** | Aligns with future AI worker | New process in Docker |
| **B. Symfony Messenger** | Stays in PHP, uses existing stack | Less like future Python AI worker |
| **C. Symfony console + cron loop** | Simplest for dev | Not production-grade |

**Recommendation:** Option A if `worker/` scaffold exists; else Option B for Sprint 3 speed, migrate to Python in Sprint 5.

---

# Docker

Add worker service to `docker-compose.yml`:

```yaml
worker:
  build: ./worker
  depends_on: [backend, postgres]
  environment:
    DATABASE_URL: ...
    POLL_INTERVAL_SECONDS: 2
```

Worker calls backend internal API or writes directly to DB — prefer **Application layer API** (no bypassing domain).

---

# End-to-end demo

```text
1. Import PDF → Content created
2. POST /contents/{id}/process → ProcessingJob Pending
3. Worker picks up → Running → Completed
4. Processing page polls → shows progress → Completed
```

---

# Tests

| Test | Type |
| ---- | ---- |
| Worker processes Pending job | Integration |
| Job not processed twice | Integration |
| GET /processing/{id} reflects worker updates | Functional E2E |

---

# Out of scope

- MinIO
- Whisper / LLM
- Artifact creation
- Retry / dead-letter queues
- Horizontal scaling

---

# Acceptance criteria

- [ ] Worker runs in Docker Compose
- [ ] Pending jobs become Completed within ~6s
- [ ] Processing page shows live progress without client simulation
- [ ] `simulateProcessing()` removed from frontend
- [ ] Sprint 3 E2E path documented in `docs/frontend/Content Flow.md`

---

# Sprint 3 Complete

After this slice, proceed to **Sprint 4 — Artifact domain (Summary)**.
