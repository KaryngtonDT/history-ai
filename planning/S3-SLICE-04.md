# S3-SLICE-04 — Create ProcessingJob API Endpoint

Status: **Done**

Epic: **Epic 03 — Asynchronous Processing Pipeline**

Depends on: S3-SLICE-03

---

# Endpoint

```http
POST /api/contents/{contentId}/processing-jobs
Content-Type: application/json

{ "type": "summary" }
```

## Response `201`

```json
{
  "id": "uuid",
  "status": "pending",
  "progress": 0
}
```

## Errors `400`

```json
{ "error": "Invalid request" }
```

Invalid: malformed `contentId`, missing `type`, invalid `type`, malformed JSON.

---

# Created

```text
Presentation/Http/Controller/Processing/CreateProcessingJobController.php
Presentation/Http/Request/Processing/CreateProcessingJobRequest.php
Presentation/Http/Request/Processing/Exception/InvalidProcessingRequestException.php
Presentation/Http/Response/Processing/CreateProcessingJobResponse.php

tests/Functional/Processing/CreateProcessingJobControllerTest.php
```

---

# curl example

```bash
curl -X POST http://localhost:8000/api/contents/{contentId}/processing-jobs \
  -H "Content-Type: application/json" \
  -d '{"type":"summary"}'
```

---

# Next

**S3-SLICE-05** — `GET /api/processing/{id}` status endpoint
