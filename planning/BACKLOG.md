# Product Backlog

Version: 2.0

Status: Active

---

# Milestone 2 — Product Experience

| Phase | Epic / Feature | Status |
| ----- | -------------- | ------ |
**Phase 0–A merged into MVP Sprint 1 ✅** — see [MVP-SPRINT-01.md](MVP-SPRINT-01.md)

| Phase | Name | Status |
| ----- | ---- | ------ |
| **1** | Navigation shell + mock data | **Done** |
| **2** | Real Content Flow (API integration) | **Done** — [SPRINT-02.md](SPRINT-02.md) |
| **3** | ProcessingJob + Worker (simulated) | **Current** — [SPRINT-03.md](SPRINT-03.md) |
| **4** | Artifact (Summary) | Planned |
| **5** | Real MinIO upload | Planned |
| **6** | Real Whisper / AI | Planned |
| **B** | [Feature-0001 PDF Upload](Epics/Epic-01-Content-Ingestion/Feature-0001-PDF-Upload/FEATURE.md) | Contract Proposed |
| **C** | Feature-0002 YouTube Import | Planned |
| **D** | Library (vertical Feature) | Planned |
| **E** | Processing Dashboard | Planned |

---

# Epic 1 — Content Ingestion (Phase B+)

| Feature | Name | Status |
| ------- | ---- | ------ |
| Feature-0001 | 📄 Upload PDF | Contract Proposed — **Phase B** |
| Feature-0002 | 🎥 Import YouTube | Planned — Phase C |
| Feature-0003 | 🎙 Upload Audio | Planned |
| Feature-0004 | 🎬 Upload Video | Planned |

---

# Epic 2 — AI Processing

| Feature | Name | Status |
| ------- | ---- | ------ |
| Feature-0005 | 📝 Transcript | Planned |
| Feature-0006 | 🌍 Translation | Planned |
| Feature-0007 | 📚 Summary | Planned |
| Feature-0008 | 🧠 Quiz | Planned |
| Feature-0009 | 🃏 Flashcards | Planned |
| Feature-0010 | 🎧 Podcast | Planned |

---

# Epic 3 — Learning

| Feature | Name | Status |
| ------- | ---- | ------ |
| Feature-0012 | 🔎 Search | Planned |
| Feature-0013 | 🏷 Tags | Planned |

*Library moves to Milestone 2 Phase D as vertical Feature.*

---

# Foundation (completed)

* Docker, backend DDD, frontend scaffold, worker
* Content aggregate + Create/List API
* Product Manifesto, Engineering Constitution, RFC-0001

---

# Policy

**Delivery by MVP Sprints** — no isolated TASK-0011+ fragments.

| Sprint | Demo |
| ------ | ---- |
| 1 ✅ | Clickable MVP — navigation + mock data |
| 2 ✅ | Real Content — Dashboard, Library, Import on PostgreSQL |
| 3 | ProcessingJob pipeline — start, poll, Worker completes |
| 4 | First Artifact (Summary) |
| 5 | Real MinIO upload |
| 6 | Real Whisper + AI |

See [MVP-SPRINT-01.md](MVP-SPRINT-01.md).
