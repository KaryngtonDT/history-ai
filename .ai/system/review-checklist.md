# Architecture Review Checklist

Use after Cursor implementation, before commit.

---

# Gate 1 — Structure

- [ ] Directory tree matches Task and `SYSTEM_BLUEPRINT.md`
- [ ] Naming conventions respected
- [ ] No unnecessary files
- [ ] No out-of-scope directories

---

# Gate 2 — Build

- [ ] Project compiles / builds (if applicable to Task)
- [ ] Containers start (if applicable to Task)
- [ ] Makefile / documented commands work

---

# Gate 3 — Quality

- [ ] Lint passes (if configured)
- [ ] Static analysis passes (if configured)
- [ ] Tests pass (if applicable to Task)

---

# Gate 4 — Architecture

- [ ] DDD module boundaries respected
- [ ] Allowed dependencies only
- [ ] No forbidden dependencies (Domain → framework, etc.)
- [ ] No obvious technical debt introduced
- [ ] No scope creep beyond Task

---

# Process

- [ ] Task acceptance criteria all checked
- [ ] Prompt archived in `.ai/prompts/`
- [ ] Review notes archived in `.ai/reviews/`
- [ ] One Task = one commit

---

# Outcome

| Result | Action |
| ------ | ------ |
| Pass | Commit → next Task |
| Fail | Corrections → re-review |

Save review as `.ai/reviews/{TASK-ID}-review.md`
