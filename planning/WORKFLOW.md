# Workflow — History AI

Version: 1.0

Status: Frozen

---

# Roles

## Product Owner & Lead Developer (You)

* Define product priorities
* Validate functional choices
* Launch Cursor with Task prompts
* Make commits
* Learn architecture decisions

## Cursor — Senior Software Engineer

* Implement **one Task at a time**
* Follow all documentation
* Never make architecture decisions
* Write tests when the Task requires them
* Propose improvements without applying them automatically

## ChatGPT — CTO & Software Architect

* Architecture
* Sprint and Task breakdown
* Code review
* Architecture review
* Optimization
* Technical debt detection
* Product evolution guidance

---

# Development Cycle

Every Task follows this cycle. **No exceptions.**

```text
1. Planning
2. Prompt Cursor
3. Implementation
4. Architecture Review
5. Corrections
6. Quality Gate
7. Commit
8. Next Task
```

---

# Task Template

Every Task file in `planning/Milestone-XX/` must contain:

* ID
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

## Gate 1 — Structure

* Correct directory tree
* Naming conventions respected
* No unnecessary files

## Gate 2 — Build

* Project compiles
* Containers start
* Commands work

## Gate 3 — Quality

* Lint OK
* Static analysis OK
* Tests OK

## Gate 4 — Architecture

* DDD respected
* Dependencies respected
* Conventions respected
* No obvious technical debt

---

# Rules

* **1 Task = 1 Prompt = 1 Commit = 1 Review**
* Implement Tasks — never "code the project"
* Code is the source of truth; documentation follows code
* Architecture changes: ADR → Code → Documentation
* Product changes: `FEATURES.md` + `USER_STORIES.md`

---

# Planning Layout

```text
planning/
├── WORKFLOW.md          ← this file
├── DELIVERY_ROADMAP.md
├── Milestone-01/
│   ├── Sprint-00.md
│   ├── TASK-0001.md
│   └── ...
├── Milestone-02/
└── ...
```

One Task = one file.
