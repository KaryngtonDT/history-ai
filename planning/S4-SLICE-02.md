# S4-SLICE-02 — Processing Orchestrator

Status: **Done**

Epic: **Epic 04 — Asynchronous Processing Engine**

---

# Goal

Automatically dispatch a newly created ProcessingJob to the Python worker via an orchestration port.

---

# Architecture

```text
CreateProcessingJobHandler
        │
        ▼
ProcessingOrchestratorInterface (Application Port)
        │
        ▼
HttpWorkerDispatcher (Infrastructure)
        │
        ▼
POST /jobs/execute (Worker)
        │
        ▼
ProcessingWorker.execute()
```

Test environment uses `NoOpProcessingOrchestrator`.

---

# Out of scope

- Queues (RabbitMQ, SQS, Kafka, Temporal)
- AI processing
- Frontend polling

---

# Next

**S4-SLICE-04** — Frontend polling (or S4-SLICE-03 lifecycle events)
