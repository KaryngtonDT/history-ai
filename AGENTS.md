# AI Agent Instructions

Entry point for AI assistants. Stable implementation rules live in `.ai/system/`.

---

# Before implementing

1. Read `.ai/system/cursor-system-prompt.md`
2. Read `START_HERE.md`
3. Read the current Task file in `planning/Milestone-XX/Sprint-YY/`
4. Read `.ai/context/` if relevant to the milestone
5. Read documentation referenced by the Task

---

# Standard Cursor prompt

```text
Read:
.ai/system/cursor-system-prompt.md

Implement:
planning/Milestone-01/Sprint-00/TASK-XXXX.md
```

---

# After implementing

Archive to `.ai/prompts/` and `.ai/reviews/` (see `planning/WORKFLOW.md`).

---

# Documentation precedence

Engineering → Architecture → Product → Task file → Code

Current focus: `planning/DELIVERY_ROADMAP.md`
