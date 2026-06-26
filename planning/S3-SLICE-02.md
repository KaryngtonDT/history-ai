# S3-SLICE-02 — Start Processing API

Status: **Planned**

Epic: **Epic 03 — Asynchronous Processing Pipeline**

Depends on: S3-SLICE-01

---

# Goal

Expose an endpoint that **creates** a ProcessingJob without executing processing.

---

# Endpoint

```http
POST /api/contents/{id}/process
```

## Request

No body required (Sprint 3).

## Response `201`

```json
{
  "processingJobId": "550e8400-e29b-41d4-a716-446655440000"
}
```

## Errors

| Status | When |
| ------ | ---- |
| 404 | Content not found |
| 409 | ProcessingJob already active for this content (optional — document decision) |
| 422 | Content in invalid state for processing |

---

# Application layer

```text
StartProcessingCommand(contentId)
  → StartProcessingHandler
    → load Content
    → create ProcessingJob(status: Pending)
    → persist
    → return processingJobId
```

Uses CQRS pattern matching `CreateContentHandler`.

---

# Presentation

```text
Presentation/Http/Controller/StartProcessingController.php
```

Route name: `api_contents_process`

---

# Tests

| Test | Assert |
| ---- | ------ |
| POST valid content id | 201 + UUID in response |
| POST unknown content id | 404 |
| DB row created | status = pending, progress = 0 |

Functional test with SQLite in-memory (existing pattern).

---

# Out of scope

- Worker picking up job
- Updating Content.status
- Frontend wiring

---

# Acceptance criteria

- [ ] Endpoint registered and documented
- [ ] CQRS handler + functional tests
- [ ] ProcessingJob persisted with Pending status
- [ ] No inline processing logic in controller

---

# Next

**S3-SLICE-03** — ProcessingStatus enum refinement (may merge with S3-01 if already done)
