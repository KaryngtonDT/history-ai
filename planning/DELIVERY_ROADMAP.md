# Delivery Roadmap

Version: 1.0

Status: Active

See [WORKFLOW.md](WORKFLOW.md) for roles, cycle, and quality gates.

---

# Milestones

| # | Name | Scope |
| - | ---- | ----- |
| 1 | Foundation | Repository, Infrastructure, Backend, Frontend, Worker, CI |
| 2 | Content Processing | Upload, yt-dlp, Whisper, Translation |
| 3 | Learning Package | Summary, Timeline, Glossary, Flashcards, Quiz, Podcast |
| 4 | Learning Platform | Library, Search, Progress, Resume |
| 5 | AI Tutor | Chat, Context Retrieval, RAG, Recommendations |
| 6 | Production | Monitoring, Observability, Billing, Security, Performance |

Planning detail: `planning/Milestone-XX/`

---

# Current Focus

**Milestone 1 / Sprint 0 / TASK-0001**

→ Architecture Review → Commit → TASK-0002

Task file: [Milestone-01/Sprint-00/TASK-0002.md](Milestone-01/Sprint-00/TASK-0002.md)

Cursor prompt:

```text
Read: .ai/system/cursor-system-prompt.md
Implement: planning/Milestone-01/Sprint-00/TASK-0002.md
```

---

# Milestone 1 Deliverable

```text
docker compose up → all services running, all health checks green
```

---

# Governance

* Code is the source of truth
* Architecture changes: ADR → Code → Documentation
* Product changes: FEATURES.md + USER_STORIES.md
* One Task = one file = one commit = one review
