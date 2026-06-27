# Sprint 14 — Final Verification Report

Version: 1.0

Status: Accepted

Date: 2026-06-28

---

# Executive summary

Sprint 14 delivered the **interactive Timeline** end-to-end: structured domain model, JSON projection API, frontend `TimelineService`, interactive renderer with markdown fallback, OpenAPI documentation, and architecture rules. Slice 6 changed **documentation and verification only** — no business logic in backend, frontend, or worker.

| Area | Result |
| ---- | ------ |
| Backend PHPUnit | ✅ 272 tests, 886 assertions |
| Backend architecture | ✅ 12 tests |
| Backend OpenAPI | ✅ 11 tests |
| Frontend build | ✅ OK |
| Frontend Vitest | ✅ 199 tests |
| Frontend Biome | ✅ clean |
| Worker pytest | ✅ 127 tests |
| Worker Ruff | ✅ All checks passed |
| OpenAPI / Swagger | ✅ `GET /api/timeline/{artifactId}` documented |

---

# Sprint 14 scope (slices 01–06)

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| S14-SLICE-01 | Timeline domain model (backend + frontend `TimelineParser`) | ✅ |
| S14-SLICE-02 | `GET /api/timeline/{artifactId}` JSON projection API | ✅ |
| S14-SLICE-03 | Frontend `TimelineService` + Http/Mock repositories | ✅ |
| S14-SLICE-04 | `InteractiveTimeline`, `TimelineArtifactRenderer`, markdown fallback | ✅ |
| S14-SLICE-05 | OpenAPI schemas, architecture rules, docs | ✅ |
| S14-SLICE-06 | Full verification + this report | ✅ |

---

# Executed commands and results

## Backend

```bash
docker compose exec backend php bin/phpunit
```

```
OK (272 tests, 886 assertions)
Time: ~5.5s
```

```bash
docker compose exec backend php bin/phpunit tests/Architecture
```

```
OK (12 tests, 13 assertions)
Time: ~0.02s
```

```bash
docker compose exec backend php bin/phpunit tests/Functional/OpenApi/
```

```
OK (11 tests, 80 assertions)
Time: ~1.4s
```

## Frontend

```bash
docker compose exec frontend npm run build
```

```
✓ built in ~13s
dist/assets/index-*.js   ~309 kB │ gzip: ~95 kB
```

```bash
docker compose exec frontend npm test
```

```
Test Files  45 passed (45)
Tests       199 passed (199)
Duration    ~40s
```

```bash
docker compose exec frontend npm run check
```

```
Checked 304 files. No fixes applied.
```

## Worker

```bash
docker compose exec worker pytest
```

```
127 passed, 1 warning in ~2.4s
```

```bash
docker compose exec worker ruff check .
```

```
All checks passed!
```

---

# Timeline architecture

## Domain (backend + frontend)

```text
Timeline
  └── sections[]
        └── title
        └── events[]
              └── text
```

| Layer | Backend | Frontend |
| ----- | ------- | -------- |
| Domain | `backend/src/Domain/Timeline/` | `frontend/src/domain/timeline/` |
| Parser | `TimelineParser.php` | `TimelineParser.ts` |
| Purity | No Symfony, Doctrine, Infrastructure, Presentation | No React, HTTP, services |

## JSON projection (backend)

```text
GET /api/timeline/{artifactId}
        │
        ▼
GetTimelineController
        │
        ▼
GetTimelineHandler
        │
        ├── ArtifactRepository (load artifact)
        └── TimelineParser (markdown → Timeline)
        │
        ▼
TimelineResponse { sections[].events[].text }
```

Responses: **200** structured timeline, **400** invalid UUID, **404** timeline artifact not found.

## Frontend service layer

```text
TimelineArtifactRenderer
        │
        ▼
TimelineService.getTimeline(artifactId)
        │
        ▼
TimelineRepositoryFactory → HttpTimelineRepository | MockTimelineRepository
        │
        ▼
HttpClient (HTTP mode only)
```

Architecture rules enforced:

- Features may import `TimelineService` only (not `HttpTimelineRepository`).
- `InteractiveTimeline` is props-only — no service imports.

## Interactive rendering

```text
TimelineArtifactRenderer
        │
        ├── loading → Spinner
        ├── Timeline JSON → InteractiveTimeline (sections + events)
        └── null / error → markdown fallback (existing parser)
```

Library details use `readOnly` — no Save to Library button.

## Worker pipeline (unchanged in Sprint 14)

```text
ProcessingService.execute()
        │
        ▼
TimelineArtifactGenerator
        │
        ▼
POST /api/internal/artifacts (type: timeline, markdown content)
```

Worker tests for `TimelineArtifactGenerator` remain green (7 tests in `test_timeline_artifact_generator.py`).

---

# Library integration

Sprint 14 builds on Sprint 13 Library flow — no regression:

```text
Timeline artifact → SaveToLibraryAction → LibraryItem (type=timeline)
        │
        ▼
LibraryItemPage → TimelineArtifactRenderer (readOnly)
        │
        ▼
TimelineService → InteractiveTimeline or markdown fallback
```

---

# OpenAPI / Swagger status

Verified via `tests/Functional/OpenApi/ApiDocumentationTest.php`:

| Check | Status |
| ----- | ------ |
| `/api/docs` (Swagger UI) available | ✅ |
| `/api/docs.json` generates OpenAPI 3.1 | ✅ |
| `GET /api/timeline/{artifactId}` documented | ✅ |
| Path parameter `artifactId` (uuid) | ✅ |
| Response 200 → `#/components/schemas/Timeline` | ✅ |
| Response 400 → `ErrorResponse` | ✅ |
| Response 404 → `ErrorResponse` | ✅ |
| Schema `Timeline` with `sections[]` | ✅ |
| Schema `TimelineSection` with `title`, `events[]` | ✅ |
| Schema `TimelineEvent` with `text` | ✅ |

**New shared schemas (Presentation only):**

| Schema | File |
| ------ | ---- |
| `Timeline` | `backend/src/Presentation/OpenApi/Schema/Timeline.php` |
| `TimelineSection` | `backend/src/Presentation/OpenApi/Schema/TimelineSection.php` |
| `TimelineEvent` | `backend/src/Presentation/OpenApi/Schema/TimelineEvent.php` |

Browse locally: `http://localhost:8000/api/docs`

---

# Architecture rules (Sprint 14 additions)

## Backend

| Rule | Scope | Enforced by |
| ---- | ----- | ----------- |
| Timeline domain purity | `Domain/Timeline` | `testTimelineDomainDoesNotDependOnOuterLayersOrFrameworks` |
| Timeline application isolation | `Application/Timeline` | `testTimelineApplicationMayDependOnTimelineDomainOnly` |
| Timeline presentation boundary | Controller, Response, OpenAPI schemas | `testTimelinePresentationMayDependOnTimelineApplicationOnly` |

## Frontend

| Rule | Enforced by |
| ---- | ----------- |
| No `HttpTimelineRepository` in features | `findFeatureTimelineTransportViolations` |
| `InteractiveTimeline` props-only | `findInteractiveTimelineServiceViolations` |
| `fetch()` centralized in `HttpClient` | `findFetchViolations` |

Documented in `docs/architecture/architecture-rules.md`.

---

# Documentation verification

| Document | Location | Status |
| -------- | -------- | ------ |
| OpenAPI guide | `docs/architecture/openapi.md` | ✅ Timeline endpoint + schemas |
| Architecture rules | `docs/architecture/architecture-rules.md` | ✅ Timeline backend + frontend rules |
| Architecture README | `docs/architecture/README.md` | ✅ Updated (Sprint 14 note) |
| Sprint 14 report | `docs/reports/Sprint14-Verification.md` | ✅ This document |

---

# Functional coverage (Sprint 14)

| Capability | Backend | Frontend | OpenAPI |
| ---------- | ------- | -------- | ------- |
| Parse timeline markdown to structured model | ✅ | ✅ | — |
| Expose JSON projection API | ✅ | — | ✅ |
| Load timeline via TimelineService | — | ✅ | — |
| Interactive section/event rendering | — | ✅ | — |
| Markdown fallback (null / error / invalid UUID) | — | ✅ | — |
| Loading state while fetching JSON | — | ✅ | — |
| Library readOnly (no Save) | — | ✅ | — |
| Architecture transport guards | ✅ | ✅ | — |

---

# Test summary (delta from Sprint 13)

| Suite | Sprint 13 | Sprint 14 | Delta |
| ----- | --------- | --------- | ----- |
| Backend PHPUnit | 249 | 272 | +23 |
| Backend architecture | 9 | 12 | +3 |
| Backend OpenAPI | 8 | 11 | +3 |
| Frontend Vitest | 164 | 199 | +35 |
| Worker pytest | 127 | 127 | — |

New frontend architecture tests: Timeline transport + InteractiveTimeline props-only (+2 rules, 6 total architecture tests).

---

# Known limitations

1. **No date parsing** — events are free-text strings; no chronological sorting or period filters beyond section order.
2. **No geographic data** — events have no coordinates; map integration is a future sprint.
3. **No cross-artifact links** — Timeline, Summary, Quiz, and Flashcards remain independent views.
4. **Markdown fallback required** — non-UUID artifact IDs (mock mode) and API errors fall back to inline markdown parsing.
5. **Backend image rebuild required** — backend has no bind mount; code changes need `docker compose build backend && docker compose up -d backend --force-recreate`.
6. **Podcast documented but not generated** — OpenAPI lists `podcast`; worker does not produce it yet.
7. **React `act(...)` warnings** — non-blocking stderr in Processing/Import page tests (pre-existing).

---

# Recommendations for Sprint 15

Infrastructure is mature (Clean Architecture, CQRS, OpenAPI, architecture tests, CI, worker pipeline, Library, Collections, Search, interactive Timeline). Sprint 15 should prioritize **user-visible value**:

| Priority | Feature | Rationale |
| -------- | ------- | --------- |
| 🥇 | **Interactive Historical Map** | Leverages structured timeline events; strong product differentiation |
| 🥈 | **Cross-artifact relations** | Timeline ↔ Summary ↔ Quiz ↔ Flashcards navigation |
| 🥉 | **Semantic search (RAG)** | Search across Library + artifact content beyond title match |

**Recommendation:** Start with the **Interactive Historical Map** — it directly extends the structured Timeline delivered in Sprint 14 without architectural rework.

---

# CTO criteria (Sprint 14 closure)

| Criterion | Status |
| --------- | ------ |
| No business logic modified in final slice | ✅ |
| All test suites green | ✅ |
| Architecture tests green | ✅ |
| OpenAPI tests green | ✅ |
| Documentation up to date | ✅ |
| Verification report generated | ✅ |

**Sprint 14 is officially closed.**

---

# Related commits (main branch)

| Commit | Message |
| ------ | ------- |
| `8985639` | feat(timeline): add timeline domain model |
| `560f561` | feat(timeline): expose timeline json projection |
| `dde7dcc` | fix(frontend): add missing timelinePath export for timeline service |
| `5a58da4` | feat(frontend): render interactive timeline |
| `2d7b8ca` | docs(timeline): document timeline api and architecture rules |

---

# Sign-off

Verified by: automated suite execution + OpenAPI schema tests

Environment: Docker Compose (backend PHP 8.4, frontend Node 22, worker Python 3.13)

Next sprint: **Sprint 15** — user-facing value (historical map, cross-artifact links, RAG search)
