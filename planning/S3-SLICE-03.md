# S3-SLICE-03 — ProcessingStatus Lifecycle

Status: **Planned**

Epic: **Epic 03 — Asynchronous Processing Pipeline**

Depends on: S3-SLICE-01

---

# Goal

Formalize and enforce the ProcessingJob lifecycle in the domain layer.

---

# ProcessingStatus

```text
Pending
Running
Completed
Failed
```

PHP enum: `ProcessingStatus` with string backing values matching API JSON (`pending`, `running`, `completed`, `failed`).

---

# Domain methods

On `ProcessingJob` aggregate:

```text
start()      Pending  → Running   (throws if not Pending)
complete()   Running  → Completed (sets progress = 100)
fail(reason) Running  → Failed
updateProgress(progress, currentStep)  Running only
```

Invalid transitions throw domain exceptions (e.g. `InvalidProcessingTransition`).

---

# Tests

| Transition | Expected |
| ---------- | -------- |
| Pending → Running | OK |
| Running → Completed | OK, progress = 100 |
| Running → Failed | OK |
| Pending → Completed | Exception |
| Completed → Running | Exception |
| Failed → Running | Exception |

100% unit coverage on transition matrix.

---

# Note

If S3-SLICE-01 already implements this fully, this slice is a **review + test hardening** slice only. Mark Done with no code changes if criteria already met.

---

# Out of scope

- HTTP
- Worker
- Frontend

---

# Acceptance criteria

- [ ] All four statuses defined
- [ ] Transition rules enforced in aggregate
- [ ] Unit tests cover invalid transitions
- [ ] Domain exceptions are typed (not generic `\Exception`)

---

# Next

**S3-SLICE-04** — `GET /api/processing/{id}`
