# .ai — AI Collaboration Workspace

Development process layer — **not product documentation**.

---

# Repository zones

| Path | Purpose |
| ---- | ------- |
| `docs/` | Product documentation |
| `engineering/` | Engineering standards and ADRs |
| `planning/` | Backlog, milestones, tasks |
| `.ai/` | AI collaboration |

---

# Structure

```text
.ai/
├── system/              ← stable rules (rarely change)
│   ├── cursor-system-prompt.md
│   ├── review-checklist.md
│   ├── architecture-rules.md
│   └── task-template.md
├── prompts/             ← archived Task prompts
├── reviews/             ← architecture review outputs
└── context/             ← shared snapshots and exploratory decisions
```

**Rule:** System prompts are stable. Tasks change. Never mix both in one prompt.

---

# Cursor invocation (standard)

```text
Read:
.ai/system/cursor-system-prompt.md

Implement:
planning/Milestone-01/Sprint-00/TASK-XXXX.md
```

---

# Naming

| Folder | Pattern | Example |
| ------ | ------- | ------- |
| `prompts/` | `{TASK-ID}.md` | `TASK-0002.md` |
| `reviews/` | `{TASK-ID}-review.md` | `TASK-0002-review.md` |
| `context/` | descriptive name | `platform-vision-draft.md` |

---

# Workflow

1. Architect writes Task in `planning/`
2. Lead Developer invokes Cursor with system prompt + Task path
3. Archive prompt → `.ai/prompts/`
4. Architecture review → `.ai/reviews/`
5. Commit

Exploratory decisions → `.ai/context/` until ADR in `engineering/ADR/`.
