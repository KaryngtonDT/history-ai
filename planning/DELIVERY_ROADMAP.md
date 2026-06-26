# Delivery Roadmap

Version: 2.1

Status: Active

See [WORKFLOW.md](WORKFLOW.md) for roles, cycle, and quality gates.

---

# Delivery Model

```text
Feature Contract → Vertical Implementation (UI + Frontend + API + Domain + Tests)
```

Backlog: [BACKLOG.md](BACKLOG.md)

Planning: [README.md](README.md)

---

# Milestone 2 — Product Experience

| Phase | Name | Scope | Status |
| ----- | ---- | ----- | ------ |
| **0** | Product Design | Design System, wireframes, mockups | **Current** |
| A | UI Foundation | Storybook, components | Next |
| B | PDF Upload | Feature-0001 vertical slice | Planned |
| C | YouTube Import | Feature-0002 | Planned |
| D | Library | Library vertical slice | Planned |
| E | Processing | Processing Dashboard | Planned |

---

# Milestones (overview)

| # | Name | Scope |
| - | ---- | ----- |
| 1 | Foundation | Infrastructure, Backend, Frontend scaffold, Worker — **Complete** |
| 2 | Product Experience | UI Foundation + Content Ingestion Features — **In Progress** |
| 3 | AI Processing | Transcript, Summary, Quiz, Podcast |
| 4 | Learning Platform | Search, Tags, Tutor |
| 5 | Production | CI/CD, Security, Observability |

---

# Current Focus

**Sprint 3 — Processing Domain** ([SPRINT-03.md](SPRINT-03.md))

Introduce `ProcessingJob` aggregate, start/status API, real Processing page, simulated Worker.

Sprint 2 (Real Content Flow) — **Complete** ([SPRINT-02.md](SPRINT-02.md))

---

# Sprint Roadmap (MVP → Pipeline)

| Sprint | Focus | Status |
| ------ | ----- | ------ |
| 1 | Clickable MVP (navigation + mocks) | **Complete** |
| 2 | Real Content Flow (API integration) | **Complete** |
| 3 | ProcessingJob + Worker (simulated) | **Current** |
| 4 | Artifact domain (Summary) | Planned |
| 5 | MinIO + real file upload | Planned |
| 6 | AI pipeline (Whisper, LLM, Quiz, …) | Planned |

---

# Governance

* Product: [PRODUCT_MANIFESTO](../docs/00_PROJECT/PRODUCT_MANIFESTO.md)
* Engineering: [Constitution](../engineering/00_ENGINEERING_PRINCIPLES.md)
* Architecture: [RFC-0001](../docs/06_RFC/RFC-0001-content-processing-pipeline.md)
* UI Features use `COMPONENT_CATALOG.md` instead of `API_CONTRACT.md`
