# S3-SLICE-05 — Get ProcessingJob API Endpoint

Status: **Done**

Epic: **Epic 03 — Asynchronous Processing Pipeline**

Depends on: S3-SLICE-04

---

# Endpoint

```http
GET /api/processing-jobs/{id}
```

## Response `200`

```json
{
  "id": "uuid",
  "contentId": "uuid",
  "type": "summary",
  "status": "pending",
  "progress": 0,
  "startedAt": null,
  "completedAt": null,
  "failedAt": null
}
```

## Errors

| Status | Body |
| ------ | ---- |
| 400 | `{ "error": "Invalid request" }` |
| 404 | `{ "error": "Processing job not found" }` |

---

# Created

```text
Application/Processing/
  Queries/GetProcessingJobQuery.php
  Handlers/GetProcessingJobHandler.php
  DTO/GetProcessingJobResult.php

Presentation/Http/
  Controller/Processing/GetProcessingJobController.php
  Response/Processing/GetProcessingJobResponse.php

tests/Unit/Application/Processing/GetProcessingJobHandlerTest.php
tests/Functional/Processing/GetProcessingJobControllerTest.php
```

---

# Next

**S3-SLICE-06** — Worker (simulated lifecycle)
