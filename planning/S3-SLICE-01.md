# S3-SLICE-01 — ProcessingJob Domain (Backend)

Status: **Done**

Epic: **Epic 03 — Asynchronous Processing Pipeline**

Depends on: Sprint 2 complete

---

# Goal

Create a **rich** `ProcessingJob` domain — pure DDD, no infrastructure.

One Content can have many independent jobs (Summary, Quiz, Podcast, …).

---

# Domain layout

```text
Domain/Processing/
  ProcessingJob.php                    aggregate root
  ProcessingJobId.php                  UUID value object
  ProcessingJobStatus.php              Pending | Running | Completed | Failed | Cancelled
  ProcessingJobType.php                Summary | Quiz | Flashcards | …
  ProcessingJobProgress.php            0..100 value object
  ProcessingJobRepositoryInterface.php
  Exception/
    InvalidProcessingJobException.php
```

---

# Model

```text
Content
  ├── ProcessingJob #1  (type = Summary)
  ├── ProcessingJob #2  (type = Quiz)
  └── ProcessingJob #3  (type = Podcast)
```

Each job is an independent execution with its own lifecycle.

---

# Aggregate API

```php
ProcessingJob::create(id, contentId, type)
ProcessingJob::start()
ProcessingJob::updateProgress(ProcessingJobProgress)
ProcessingJob::complete()
ProcessingJob::fail()
ProcessingJob::cancel()
```

Timestamps (domain-only, not persisted yet): `startedAt`, `completedAt`, `failedAt`.

---

# Progress invariants

| Status | Progress |
| ------ | -------- |
| Pending | always 0 |
| Running | `updateProgress`: strictly 0 < n < 100, strictly increasing |
| Completed | always 100 (set by `complete()`) |
| Failed / Cancelled | no further updates |

`ProcessingJobProgress::fromPercentage(130)` → exception.

Use `complete()` to reach 100 — not `updateProgress(100)`.

---

# Lifecycle

```text
Pending → Running → updateProgress → … → complete() → Completed
Pending → Running → fail() → Failed
Pending → cancel() → Cancelled
```

---

# Tests

`tests/Unit/Domain/Processing/ProcessingJobTest.php` — 36 tests:

- Multi-job per Content (different types)
- Worker happy path (5 → 17 → 39 → 61 → 88 → complete)
- Progress VO bounds (negative, >100, 130)
- Decreasing / same progress rejected
- Progress after terminal status rejected
- All invalid status transitions rejected

---

# Explicitly out of scope (deferred)

- DoctrineProcessingJobRepository
- SQL migration
- REST API
- Worker Python
- Message queue
- Domain events

---

# Acceptance criteria

- [x] Aggregate + value objects with domain tests
- [x] ProcessingJobProgress value object
- [x] Progress and timestamp invariants
- [x] Repository interface only (no implementation)
- [x] All backend tests green
- [x] No Symfony / Doctrine in domain layer

---

# Next

**S3-SLICE-02** — `POST /api/contents/{id}/process` (requires Doctrine + migration in that slice or a dedicated persistence slice)
