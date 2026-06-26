# Planning — History AI

Version: 2.1

Status: Active

---

# Delivery Model

History AI delivers **vertical features** with **contract-first** design.

```text
Product Manifesto
        ↓
RFC
        ↓
Epic
        ↓
Feature Contract (4 documents)
        ↓
Technical Tasks
        ↓
Code
        ↓
Review
```

---

# Feature Contract

Before implementation, every Feature must have:

| Document | Purpose |
| -------- | ------- |
| `FEATURE.md` | Goal, value, actors, success criteria |
| `STORIES.md` | User stories |
| `ACCEPTANCE_TESTS.md` | Testable scenarios |
| `API_CONTRACT.md` | Stable API surface (backend Features) |
| `COMPONENT_CATALOG.md` | Components, tokens, pages (UI Features) |

UI-only Features (Phase A) use `COMPONENT_CATALOG.md` instead of `API_CONTRACT.md`.

Only after contract **validation** are technical tasks created in `tasks/`.

Frontend and backend can then work in parallel against the same contract.

---

# Folder Layout

```text
planning/
├── README.md
├── BACKLOG.md
├── WORKFLOW.md
├── DELIVERY_ROADMAP.md
│
├── Epics/
│   └── Epic-01-Content-Ingestion/
│       ├── EPIC.md
│       └── Feature-0001-PDF-Upload/
│           ├── FEATURE.md
│           ├── STORIES.md
│           ├── ACCEPTANCE_TESTS.md
│           ├── API_CONTRACT.md
│           └── tasks/              ← after contract validation
│
└── Milestone-XX/                   ← legacy
```

---

# Current Focus

**Sprint 3 — Processing Domain** — [SPRINT-03.md](SPRINT-03.md)

Sprint 2 closed — [SPRINT-02.md](SPRINT-02.md) · Architecture review — [SPRINT-02-ARCHITECTURE-REVIEW.md](SPRINT-02-ARCHITECTURE-REVIEW.md)

Feature contracts for PDF Upload remain valid for Phase B+ vertical slices.

---

# Rules

* **No code until Feature Contract is validated**
* **1 Feature = end-to-end user capability**
* **1 Task = 1 Prompt = 1 Commit = 1 Review**
* Tasks must reference `ACCEPTANCE_TESTS.md` and `API_CONTRACT.md`
* Legacy Milestone tasks (TASK-0007–0010) remain valid foundation

---

# Cursor Prompt (after contract validation)

```text
Read AGENTS.md.
Read START_HERE.md.
Read docs/00_PROJECT/PRODUCT_MANIFESTO.md.
Read engineering/00_ENGINEERING_PRINCIPLES.md.
Read docs/06_RFC/RFC-0001-content-processing-pipeline.md.
Read Feature-XXXX: FEATURE.md, STORIES.md, ACCEPTANCE_TESTS.md, API_CONTRACT.md.
Implement ONLY the current Task in tasks/.
```
