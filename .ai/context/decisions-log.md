# Decision 001 — AI Workspace

Moved from `.ai/decisions/`. See `.ai/README.md`.

**Status:** Accepted

Create `.ai/` for prompts, reviews, context, and stable system prompts in `.ai/system/`.

---

# Decision 002 — System Prompts (pending)

**Status:** Accepted

Stop long ad-hoc Cursor prompts. Stable rules live in `.ai/system/`. Tasks live in `planning/`. Cursor invocation:

```text
Read: .ai/system/cursor-system-prompt.md
Implement: planning/Milestone-XX/Sprint-YY/TASK-XXXX.md
```

---

# Decision 003 — Learning Platform Vision (v2)

**Status:** Accepted — 2026-06-25

**Decision:** History AI is the first product on the Learning Platform. Core domain = learning. History = Subject specialization.

**Repo:** Keep `history-ai` on GitHub.

**Code:** Backend modules (Learning, Content, Knowledge, Tutor, Library, User) already platform-oriented per SYSTEM_BLUEPRINT.

**Deferred:** Full DOMAIN_MODEL rewrite, vertical modules (`modules/history/`), platform rename — post Milestone 1.

**Doc updated:** `docs/00_PROJECT/VISION.md` v2.0 only. No further product docs until Feature-driven changes.

---

# Decision 004 — Stop Conception, Start Delivery

**Status:** Accepted — 2026-06-25

Product and architecture baseline sufficient for months of development.

**Rule:** One session = one Task. No new docs unless required by current Task.

**Next:** TASK-0002 review → commit → TASK-0003.
