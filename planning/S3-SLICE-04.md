# S3-SLICE-04 — Processing Status API

Status: **Planned**

Epic: **Epic 03 — Asynchronous Processing Pipeline**

Depends on: S3-SLICE-02, S3-SLICE-03

---

# Goal

Expose read endpoint for processing progress. Progress is **simulated** until real pipeline steps exist.

---

# Endpoint

```http
GET /api/processing/{id}
```

## Response `200`

```json
{
  "status": "running",
  "progress": 42,
  "currentStep": "Extracting text"
}
```

## Errors

| Status | When |
| ------ | ---- |
| 404 | ProcessingJob not found |

---

# Simulated progress (Sprint 3 only)

Until Worker (S3-06) drives real updates, backend may compute progress from elapsed time since `started_at`:

```text
Pending   → progress 0,   currentStep: "Pending"
Running   → progress 1–99 (time-based), currentStep: "Extracting text"
Completed → progress 100, currentStep: "Completed"
Failed    → progress 0,   currentStep: "Failed"
```

Alternative: return stored DB values only (Worker updates in S3-06). Document chosen approach in slice PR.

---

# Application layer

```text
GetProcessingStatusQuery(processingJobId)
  → GetProcessingStatusHandler
    → load ProcessingJob
    → map to DTO
```

---

# Presentation

```text
Presentation/Http/Controller/GetProcessingStatusController.php
```

Route: `GET /api/processing/{id}`

---

# Tests

| Test | Assert |
| ---- | ------ |
| GET existing job (Pending) | status pending, progress 0 |
| GET after Worker marks Running | status running, progress > 0 |
| GET unknown id | 404 |

---

# Out of scope

- Frontend polling implementation (S3-05)
- Real extraction / AI steps
- WebSocket / SSE (polling is fine for Sprint 3)

---

# Acceptance criteria

- [ ] Endpoint returns stable JSON contract
- [ ] Functional tests green
- [ ] Response uses domain status values (lowercase strings)
- [ ] No frontend changes in this slice

---

# Next

**S3-SLICE-05** — Wire Processing page to real API
