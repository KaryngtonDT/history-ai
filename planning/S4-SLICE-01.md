# S4-SLICE-01 — Worker Execution Pipeline

Status: **Done**

Epic: **Epic 04 — Asynchronous Processing Engine**

---

# Goal

First async pipeline: Worker calls Symfony internal API to transition ProcessingJob lifecycle.

---

# Backend — Internal API

| Method | Path | Handler |
|--------|------|---------|
| POST | `/internal/processing-jobs/{id}/start` | `StartProcessingJobHandler` |
| POST | `/internal/processing-jobs/{id}/progress` | `UpdateProcessingProgressHandler` |
| POST | `/internal/processing-jobs/{id}/complete` | `CompleteProcessingJobHandler` |

Application commands delegate to domain methods (`start()`, `updateProgress()`, `complete()`).

---

# Worker

```text
POST /processing-jobs/{id}/execute
  → ProcessingWorker.execute()
  → start → sleep → progress(20,45,80) → sleep → complete
```

Env: `SYMFONY_API_BASE_URL=http://backend`

---

# Out of scope

- RabbitMQ, Redis queue, Celery, Kafka
- AI, OCR, Whisper, LLM
- Frontend polling

---

# Next

**S4-SLICE-02** — Dispatcher (trigger worker after job creation)
