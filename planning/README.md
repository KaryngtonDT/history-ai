# Planning — Lumen / Shadow

Version: 3.0

Status: Active

Constitutional reference: [LUMEN_VISION_2030.md](../docs/vision/LUMEN_VISION_2030.md)

---

# Delivery model (Shadow sprints)

```text
LUMEN_VISION_2030.md
        ↓
Shadow Roadmap (docs/shadow/ROADMAP.md)
        ↓
TASK-00XX.md (planning/Shadow/Sprint-XX/)
        ↓
Implementation + SprintXX-Verification.md
```

Early Epic/Milestone contracts (PDF Upload, Design System) remain valid **historical foundation** — see [Legacy planning](#legacy-planning).

---

# Folder layout

```text
planning/
├── README.md
├── DELIVERY_ROADMAP.md          ← active roadmap
├── WORKFLOW.md
├── BACKLOG.md
│
├── Shadow/                      ← **current Shadow sprints**
│   ├── README.md
│   └── Sprint-XX/TASK-00XX.md
│
├── Epics/                       ← legacy feature contracts (foundation)
└── Milestone-XX/                ← legacy infrastructure tasks
```

---

# Current focus

**Sprint 68 — Shadow Everywhere Foundation**

- [TASK-0068](Shadow/Sprint-68/TASK-0068.md)
- [DELIVERY_ROADMAP.md](DELIVERY_ROADMAP.md)

Sprint 67 (Second Brain) — **Complete**.

---

# Legacy planning

These folders document early MVP and infrastructure work. They are **not** the active model for Shadow Phase III:

- `Milestone-01/` … `Milestone-06/`
- `MVP-SPRINT-01*`, `SPRINT-02.md`, `SPRINT-03.md`
- `Epics/Epic-00-UI-Foundation/`, `Epics/Epic-01-Content-Ingestion/`

Retain for audit; do not treat as current product direction.

---

# Rules

- Align every Shadow sprint with [LUMEN_VISION_2030.md](../docs/vision/LUMEN_VISION_2030.md)
- 1 Task = 1 focused implementation cycle
- Docker validation gate required before sprint sign-off
- Update `docs/reports/SprintXX-Verification.md` on completion

---

# Cursor prompt

```text
Read AGENTS.md.
Read docs/vision/LUMEN_VISION_2030.md.
Implement ONLY planning/Shadow/Sprint-XX/TASK-00XX.md.
```
