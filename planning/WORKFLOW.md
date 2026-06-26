# Workflow — History AI

Version: 2.0

Status: Active

---

# Roles

## Product Owner & Lead Developer (You)

* Define product priorities and Features
* Validate functional choices
* Launch Cursor with Task prompts (within a Feature)
* Make commits
* Learn architecture decisions

## Cursor — Senior Software Engineer

* Implement **one Task at a time** within the current Feature
* Follow all documentation
* Never make architecture decisions
* Write tests when the Task requires them
* Propose improvements without applying them automatically

## ChatGPT — CTO & Software Architect

* Architecture
* Epic / Feature / Story / Task breakdown
* Code review
* Architecture review
* Optimization
* Technical debt detection
* Product evolution guidance

---

# Delivery Hierarchy

```text
Product Manifesto
  ↓
Engineering Constitution
  ↓
RFC → ADR
  ↓
Epic
  ↓
Feature Contract
  ├── FEATURE.md
  ├── STORIES.md
  ├── ACCEPTANCE_TESTS.md
  └── API_CONTRACT.md
  ↓
Technical Task
  ↓
Code
```

---

# Feature Contract Gate

**No technical Task is created or implemented until the four contract documents are validated.**

Contract validation confirms:

* User flow is defined
* Stories cover the flow
* Acceptance tests are written
* API contract is stable

Then tasks are added under `tasks/` and implementation begins.

---

# Development Cycle

Every **Task** follows this cycle. **No exceptions.**

```text
1. Planning (Feature + Story context)
2. Prompt Cursor
3. Implementation
4. Architecture Review (Manifesto → RFC → Constitution → Code)
5. Corrections
6. Quality Gate
7. Commit
8. Next Task (same Feature until Feature DoD)
```

A **Feature** is complete when all Stories and its Definition of Done pass.

---

# Task Template

Every Task file under `planning/Epics/.../Feature-XXXX/tasks/` must contain:

* ID
* Feature and Story reference
* Objective
* Context
* Prerequisites
* Constraints
* Deliverables
* Acceptance Criteria
* Out of Scope
* Definition of Done

---

# Quality Gates

A Task is complete only when **all applicable gates** pass.

## Gate 1 — Product

* Aligns with Product Manifesto
* Improves learning or ingestion path (not hype)

## Gate 2 — Structure

* Correct directory tree
* Naming conventions respected
* No unnecessary files

## Gate 3 — Build

* Project compiles
* Containers start
* Commands work

## Gate 4 — Quality

* Lint OK
* Static analysis OK
* Tests OK (zero PHPUnit notices)

## Gate 5 — Architecture

* RFC and ADR respected
* Engineering Constitution respected
* DDD respected
* Dependencies respected
* No obvious technical debt

---

# Rules

* **1 Feature = vertical slice** (backend + frontend + infra needed for user value)
* **1 Task = 1 Prompt = 1 Commit = 1 Review**
* Implement Tasks — never "code the whole Feature" in one prompt
* Code is the source of truth; documentation follows code
* Architecture changes: RFC → ADR → Code → Documentation
* Legacy Milestone tasks (M1, early M2) remain historical reference

---

# Repository Zones

| Path | Purpose |
| ---- | ------- |
| `docs/` | Product documentation |
| `engineering/` | Engineering Constitution and ADRs |
| `planning/` | Backlog, Epics, Features, Stories, Tasks |
| `.ai/` | AI collaboration (prompts, reviews, decisions, context) |

After each Task: save prompt to `.ai/prompts/` and review to `.ai/reviews/`.

Cursor reads `.ai/system/cursor-system-prompt.md` — not a long inline prompt.

---

# Planning Layout

```text
planning/
├── README.md
├── BACKLOG.md
├── WORKFLOW.md
├── DELIVERY_ROADMAP.md
├── Epics/
│   └── Epic-01-Content-Ingestion/
│       ├── EPIC.md
│       └── Feature-0001-PDF-Upload/
│           ├── FEATURE.md
│           ├── STORIES.md
│           ├── ACCEPTANCE_TESTS.md
│           ├── API_CONTRACT.md
│           └── tasks/
│               └── TASK-XXXX.md
└── Milestone-XX/          ← legacy
```

See [README.md](README.md) for current focus.
