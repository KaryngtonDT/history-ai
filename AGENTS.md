# AI Agent Instructions

Entry point for AI assistants. Stable implementation rules live in `.ai/system/`.

---

# Before implementing

1. Read `.ai/system/cursor-system-prompt.md`
2. Read `START_HERE.md`
3. Read `docs/00_PROJECT/PRODUCT_MANIFESTO.md`
4. Read `engineering/00_ENGINEERING_PRINCIPLES.md`
5. Read accepted RFCs in `docs/06_RFC/` referenced by the Task
6. Read the Feature contract: `FEATURE.md`, `STORIES.md`, `ACCEPTANCE_TESTS.md`, `API_CONTRACT.md`
7. Read the current Task under `planning/Epics/.../tasks/`
8. Read `.ai/context/` if relevant
9. Read documentation referenced by the Task

**Do not implement if the Feature contract is not validated.**

---

# Standard Cursor prompt

```text
Read:
.ai/system/cursor-system-prompt.md
docs/00_PROJECT/PRODUCT_MANIFESTO.md
engineering/00_ENGINEERING_PRINCIPLES.md

Read Feature contract:
planning/Epics/.../Feature-XXXX/FEATURE.md
planning/Epics/.../Feature-XXXX/STORIES.md
planning/Epics/.../Feature-XXXX/ACCEPTANCE_TESTS.md
planning/Epics/.../Feature-XXXX/API_CONTRACT.md

Implement:
planning/Epics/Epic-XX/Feature-XXXX/tasks/TASK-YYYY.md
```

---

# After implementing

Archive to `.ai/prompts/` and `.ai/reviews/` (see `planning/WORKFLOW.md`).

---

# Documentation precedence

```text
Vision
  ↓
Product Manifesto
  ↓
Engineering Principles
  ↓
Accepted RFC
  ↓
ADR (docs/05_DECISIONS/)
  ↓
Architecture / Product specs
  ↓
Feature Contract
  ↓
Task file
  ↓
Code
```

**No implementation without validated Feature Contract.**

**No Task that defines new domain behavior without an accepted RFC.**

Every feature must align with `docs/00_PROJECT/PRODUCT_MANIFESTO.md` and `engineering/00_ENGINEERING_PRINCIPLES.md`.

Current focus: **MVP Sprint 2** — fake PDF upload. See [MVP-SPRINT-01.md](planning/MVP-SPRINT-01.md) (Sprint 1 complete).
