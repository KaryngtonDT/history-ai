# Sprint 2 — Real Content Flow

Status: **Complete**

Epic: **Epic 02 — Real Content Flow**

---

# Sprint Goal

Replace screen-based mocks with a unified **Content** domain wired to the real Symfony API — one story at a time, without UI redesign.

---

# Method

**Sprint** = delivery goal · **Slice** = implementation unit (2–6 h)

No monolithic sprint prompts. One slice → review → next slice.

---

# Slices

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| **S2-01** | Unified `ContentService` + repository pattern | **Done** |
| **S2-02** | `HttpClient` + `HttpContentRepository` | **Done** |
| **S2-03** | Library → `GET /api/contents` | **Done** |
| **S2-04** | Import → `POST /api/contents` | **Done** |
| **S2-05** | Dashboard → real statistics from API | **Done** |
| **S2-06** | Architecture cleanup + documentation | **Done** |

---

# Deliverables

## Backend (unchanged this sprint)

| Domaine | État |
| ------- | ---- |
| DDD | ✅ |
| CQRS | ✅ |
| REST API Content | ✅ |
| PostgreSQL | ✅ |
| Tests | ✅ |

## Frontend

| Domaine | État |
| ------- | ---- |
| Design System | ✅ |
| Dashboard (real data) | ✅ |
| Library (real data) | ✅ |
| Import (real create) | ✅ |
| HTTP Layer (`HttpClient`) | ✅ |
| Repository Pattern | ✅ |
| `ContentService` unifié | ✅ |
| Config centralisée (`src/config/`) | ✅ |
| Erreurs typées (`shared/errors/`) | ✅ |
| DTO convention (`api/` / `domain/` / `mappers/`) | ✅ |

## Intégration

| Élément | État |
| ------- | ---- |
| `GET /api/contents` | ✅ |
| `POST /api/contents` | ✅ |
| Dashboard réel | ✅ |
| Library réelle | ✅ |
| Import réel (création Content) | ✅ |
| CORS dev (5173 → 8000) | ✅ |
| Docker build (`VITE_USE_MOCK=false`) | ✅ |

---

# Architecture at Sprint End

```text
PDF (UI only — no bytes uploaded yet)
  ↓
POST /api/contents
  ↓
Content (PostgreSQL)
  ↓
GET /api/contents
  ↓
Dashboard / Library
```

**Pipeline gap:** no `ProcessingJob`, no Worker, no `Artifact`.

---

# Explicitly Out of Scope (deferred)

| Item | Reason |
| ---- | ------ |
| MinIO | Requires ProcessingJob first |
| Whisper / LLM | Requires pipeline + Artifact |
| Flashcards / Quiz / Translation / Podcast | Requires Artifact domain |
| Processing page real API | Sprint 3 |
| Worker Python | Sprint 3 |

---

# Tech Debt (carried forward)

| Item | File |
| ---- | ---- |
| `sourceType` alignment (`pdf` ↔ `upload_pdf`) | [TECH-DEBT-sourceType-alignment.md](TECH-DEBT-sourceType-alignment.md) |
| `artifacts: 12` placeholder in dashboard stats | Fix when Artifact domain exists (Sprint 4) |
| Import: simulated upload progress, no file bytes | Fix when MinIO lands (Sprint 5+) |

---

# Validation (S2-06)

| Check | Result |
| ----- | ------ |
| Biome | ✅ |
| Vitest (46 tests) | ✅ |
| Production build | ✅ |
| No fetch outside `HttpClient` | ✅ |
| No `import.meta.env` outside `config/` | ✅ |

---

# Documentation

| Doc | Path |
| --- | ---- |
| Frontend Architecture | `docs/frontend/Frontend Architecture.md` |
| Content Flow | `docs/frontend/Content Flow.md` |
| Repository Pattern | `docs/frontend/Repository Pattern.md` |
| PR Quality Checklist | `docs/frontend/PR-QUALITY-CHECKLIST.md` |
| Architecture Review | [SPRINT-02-ARCHITECTURE-REVIEW.md](SPRINT-02-ARCHITECTURE-REVIEW.md) |

---

# Next

**[Sprint 3 — Processing Domain](SPRINT-03.md)**

Introduce `ProcessingJob` aggregate, start/status API, real Processing page, simulated Worker.
