# S3-SLICE-02 — ProcessingJob Persistence

Status: **Done**

Epic: **Epic 03 — Asynchronous Processing Pipeline**

Depends on: S3-SLICE-01

---

# Goal

Persist `ProcessingJob` with Doctrine without polluting the domain layer.

```text
ProcessingJob (domain)
  ↓
ProcessingJobRepositoryInterface
  ↓
DoctrineProcessingJobRepository
  ↓
ProcessingJobRecord
  ↓
processing_jobs table
```

---

# Created

```text
Infrastructure/Persistence/Doctrine/Processing/
  ProcessingJobRecord.php
  DoctrineProcessingJobRepository.php

migrations/Version20260626140000.php

tests/Integration/Persistence/Processing/
  DoctrineProcessingJobRepositoryTest.php
```

---

# Table `processing_jobs`

| Column | Type |
| ------ | ---- |
| id | UUID PK |
| content_id | UUID (indexed) |
| type | VARCHAR(32) |
| status | VARCHAR(32) |
| progress | SMALLINT |
| started_at | TIMESTAMP nullable |
| completed_at | TIMESTAMP nullable |
| failed_at | TIMESTAMP nullable |
| created_at | TIMESTAMP |
| updated_at | TIMESTAMP |

---

# Architectural decisions

- **Record pattern** — mirrors `ContentRecord`; domain aggregate unchanged
- **Mapping** — `fromDomain` / `syncFromDomain` / `toDomain` on record
- **Progress** — stored as integer; rehydrated via `ProcessingJobProgress::fromPercentage()`
- **No FK** — consistent with `contents` table (content_id is logical reference)
- **DI** — `ProcessingJobRepositoryInterface` aliased in `services.yaml`

---

# Out of scope

- HTTP controller / handler
- Worker / queue
- Frontend

---

# Next

**S3-SLICE-03** — Start processing API (`POST /api/contents/{id}/process`)
