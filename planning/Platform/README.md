# Platform Planning

Platform sprints cover **Lumen infrastructure** — pipeline, AI engines, runtime, storage, deployment — distinct from **Shadow product** sprints under `planning/Shadow/`.

Every platform sprint must align with [LUMEN_VISION_2030.md](../../docs/vision/LUMEN_VISION_2030.md) and the [Engineering Constitution](../../engineering/00_ENGINEERING_PRINCIPLES.md).

---

# Sprint question

> **How does Lumen know which AI engines are installed, healthy, and best for each job — and how does the UI always reflect what actually runs?**

---

# Task index

| Sprint | Task | Scope | Status |
| ------ | ---- | ----- | ------ |
| **70.4** | [TASK-0070.4](Sprint-70.4/TASK-0070.4.md) | AI Runtime, Engine Intelligence & Benchmark Platform | **Complete** |
| **70.6** | [TASK-0070.6](Sprint-70.6/TASK-0070.6.md) | Runtime completion & dashboard | **Complete** |
| **70.7** | [TASK-0070.7](Sprint-70.7/TASK-0070.7.md) | Runtime provisioning intelligence | **Complete** |
| **70.8** | [TASK-0070.8](Sprint-70.8/TASK-0070.8.md) | Background pipeline orchestration & progress UX | **In progress** |
| **70.9** | [TASK-0070.9](Sprint-70.9/TASK-0070.9.md) | Engine performance analytics & adaptive duration estimation | **Planned** |

**Note:** Platform Sprint **70.5** was merged into **70.4** (Runtime + Registry + Benchmark + Orchestrator = one system).

---

# Conventions

| Topic | Convention |
| ----- | ---------- |
| Domain prefix | `Runtime`, `Engine` (bounded contexts under `backend/src/Domain/`) |
| API base | `/api/runtime/*` |
| Settings route | `/settings/runtime` |
| Frontend features | `frontend/src/features/runtime/` |
| Models on host | `./models/` (never baked into Docker images) |
| Engines on host | Ollama, Faster Whisper, F5-TTS, OpenVoice, LatentSync, FFmpeg |
| Docker stack | Frontend, Backend, Worker, PostgreSQL, Redis only |

---

# Related

- [Delivery Roadmap](../DELIVERY_ROADMAP.md)
- [Shadow Planning](../Shadow/README.md)
- [Architecture index](../../docs/architecture/README.md)
