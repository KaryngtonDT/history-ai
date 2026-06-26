# S4-SLICE-03 — Processing Live Updates

Status: **Done**

Epic: **Epic 04 — Asynchronous Processing Engine**

---

# Goal

Automatically refresh the Processing page while a job is running, via a monitor abstraction (polling today, SSE/WebSocket later).

---

# Architecture

```text
ProcessingPage
        │
        ▼
ProcessingService
        │
        ▼
ProcessingMonitor (interface)
        │
        ├── PollingProcessingMonitor   (production)
        └── SimulatedProcessingMonitor (mock / tests)
        │
        ▼
ProcessingRepository
        │
        ▼
Symfony API
```

---

# Behavior

- Poll every 2 seconds (production)
- Stop on: completed, failed, cancelled
- Stop on component unmount (unsubscribe)
- No `setInterval` in React components

---

# Out of scope

- Backend changes
- SSE / WebSocket
- React Query / Zustand

---

# Next

**S4-SLICE-04** — SSE monitor or first real processing step
