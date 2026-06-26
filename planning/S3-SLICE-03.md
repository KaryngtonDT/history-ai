# S3-SLICE-03 — Create ProcessingJob Use Case

Status: **Done**

Epic: **Epic 03 — Asynchronous Processing Pipeline**

Depends on: S3-SLICE-02

---

# Goal

First application use case: create a `ProcessingJob` for a given Content and type.

```text
ContentId + ProcessingJobType
  ↓
CreateProcessingJobCommand
  ↓
CreateProcessingJobHandler
  ↓
ProcessingJob::create()
  ↓
ProcessingJobRepositoryInterface::save()
  ↓
CreateProcessingJobResult
```

---

# Created

```text
Application/Processing/
  Commands/CreateProcessingJobCommand.php
  Handlers/CreateProcessingJobHandler.php
  DTO/CreateProcessingJobResult.php

tests/Unit/Application/Processing/
  CreateProcessingJobHandlerTest.php
```

---

# Result DTO

| Field | Type |
| ----- | ---- |
| processingJobId | ProcessingJobId |
| status | ProcessingJobStatus (Pending) |
| progress | int (0) |

---

# Out of scope

- HTTP controller
- Worker / queue
- Content existence validation (deferred to API slice)
- Frontend

---

# Next

**S3-SLICE-04** — `POST /api/contents/{id}/process` controller wiring
