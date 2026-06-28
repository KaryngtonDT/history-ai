# Sprint 18 — Final Verification Report

Version: 1.0

Status: Accepted

Date: 2026-06-28

---

# Executive summary

Sprint 18 delivered **contextual artifact recommendations** end-to-end: recommendation domain, JSON projection API, frontend `RecommendationService`, `SeeAlsoRecommendationsPanel`, OpenAPI documentation, and architecture rules. Slice 5 changed **documentation and verification only** — no business logic in backend, frontend, or worker.

| Area | Result |
| ---- | ------ |
| Backend PHPUnit | ✅ 398 tests, 1334 assertions |
| Backend architecture | ✅ 24 tests |
| Backend OpenAPI | ✅ 23 tests |
| Frontend build | ✅ OK |
| Frontend Vitest | ✅ 309 tests |
| Frontend Biome | ✅ clean (372 files) |
| Worker pytest | ✅ 127 tests |
| Worker Ruff | ✅ All checks passed |
| OpenAPI / Swagger | ✅ `GET /api/contents/{contentId}/artifacts/{artifactId}/recommendations` documented |

---

# Sprint 18 scope (slices 01–05)

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| S18-SLICE-01 | `RecommendationEngine`, `RecommendedArtifact`, `RecommendedArtifactCollection`, `RecommendationReason` domain | ✅ |
| S18-SLICE-02 | `GET /api/contents/{contentId}/artifacts/{artifactId}/recommendations` JSON projection API | ✅ |
| S18-SLICE-03 | Frontend `RecommendationService` + Http/Mock repositories | ✅ |
| S18-SLICE-04 | `SeeAlsoRecommendationsPanel` under each artifact card | ✅ |
| S18-SLICE-05 | OpenAPI schemas, architecture docs, full verification + this report | ✅ |

---

# Executed commands and results

## Backend

```bash
docker compose run --rm --no-deps --entrypoint php \
  -v "$(pwd)/backend:/var/www/html" backend bin/phpunit
```

```
OK (398 tests, 1334 assertions)
Time: ~91s
```

```bash
docker compose run --rm --no-deps --entrypoint php \
  -v "$(pwd)/backend:/var/www/html" backend bin/phpunit tests/Architecture
```

```
OK (24 tests, 25 assertions)
Time: ~4s
```

```bash
docker compose run --rm --no-deps --entrypoint php \
  -v "$(pwd)/backend:/var/www/html" backend bin/phpunit tests/Functional/OpenApi/
```

```
OK (23 tests, 241 assertions)
Time: ~28s
```

> **Note:** The backend image has no bind mount. After slice 5 changes, run `docker compose build backend && docker compose up -d backend --force-recreate` so the running container serves updated OpenAPI annotations. Validation above used a one-off bind mount to verify workspace code.

## Frontend

```bash
docker compose exec frontend npm run build
```

```
✓ built in ~1s
dist/assets/index-*.js   ~327 kB │ gzip: ~99 kB
```

```bash
docker compose exec frontend npm test
```

```
Test Files  67 passed (67)
Tests       309 passed (309)
Duration    ~66s
```

```bash
docker compose exec frontend npm run check
```

```
Checked 372 files. No fixes applied.
```

## Worker

```bash
docker compose exec worker pytest
```

```
127 passed, 1 warning in ~2s
```

```bash
docker compose exec worker ruff check .
```

```
All checks passed!
```

---

# Recommendation domain

```text
RecommendedArtifact
  ├── artifactId (ArtifactId)
  ├── type (ArtifactType)
  ├── title (string)
  └── reason (RecommendationReason)

RecommendedArtifactCollection
  └── recommendations[]

RecommendationReason enum
  ├── related
  ├── derived_from
  ├── references
  ├── next
  └── previous
```

| Layer | Backend | Frontend |
| ----- | ------- | -------- |
| Domain | `backend/src/Domain/Recommendation/` | Consumed via API JSON |
| Purity | No Symfony, Doctrine, Infrastructure, Presentation | Panel uses `RecommendationService` only |

---

# RecommendationEngine

```text
Content artifacts (from ArtifactRepository)
        │
        ▼
ArtifactRelationResolver → ArtifactRelationCollection
        │
        ▼
KnowledgeGraphBuilder → KnowledgeGraph
        │
        ▼
RecommendationEngine.recommend(currentArtifactId)
        │
        ├── Direct graph neighbours only
        ├── One reason per neighbour (first matching edge)
        └── Empty collection when artifact unknown or isolated
        │
        ▼
RecommendedArtifactCollection
```

Engine lives in `Domain/Recommendation` and depends on `Domain/Graph`, `Domain/Artifact`, and `Domain/Relation` only.

---

# Recommendation projection API

```text
Content + Artifact
        │
        ▼
ArtifactRepository → artifacts[]
        │
        ▼
ArtifactRelationResolver → relations
        │
        ▼
KnowledgeGraphBuilder → KnowledgeGraph
        │
        ▼
RecommendationEngine → RecommendedArtifactCollection
        │
        ▼
GET /api/contents/{contentId}/artifacts/{artifactId}/recommendations
        │
        ▼
GetArtifactRecommendationsHandler
        │
        ▼
{ "recommendations": [{ "artifactId", "type", "title", "reason" }] }
```

Responses: **200** recommendations projection, **400** invalid UUID.

---

# Frontend RecommendationService

```text
ProcessingArtifacts / LibraryItemDetails
        │
        ├── artifact cards (id="artifact-{type}" anchors)
        └── SeeAlsoRecommendationsPanel (per existing artifact)
                │
                ▼
        RecommendationService.getArtifactRecommendations(contentId, artifactId)
                │
                ▼
        RecommendationRepositoryFactory → HttpRecommendationRepository | MockRecommendationRepository
                │
                ▼
        HttpClient (HTTP mode only)
```

Architecture rules enforced:

- Features may import `RecommendationService` only (not `HttpRecommendationRepository`).
- Invalid UUID contentId or artifactId → empty recommendations (graceful degradation).
- Missing artifact placeholders (e.g. "No quiz yet") do not render recommendation panels.
- `ProcessingArtifacts` still performs a single `artifactService.listByContentId()` call.

---

# SeeAlsoRecommendationsPanel

| Component | Role |
| --------- | ---- |
| `SeeAlsoRecommendationsPanel` | Fetches recommendations via `RecommendationService`; loading/empty/error states |
| `recommendationLabels.ts` | Human-readable reason labels (`Derived from`, `Next`, …) |
| Anchor links | `#artifact-summary`, `#artifact-quiz`, `#artifact-flashcards`, `#artifact-timeline`, `#artifact-transcript` |

Integrated in `ProcessingArtifacts` below each rendered artifact card when the artifact exists. Also shown in read-only `LibraryItemDetails` when `contentId` is available (without Save to Library).

---

# OpenAPI / Swagger status

Verified via `tests/Functional/OpenApi/ApiDocumentationTest.php`:

| Check | Status |
| ----- | ------ |
| `/api/docs` (Swagger UI) available | ✅ |
| `/api/docs.json` generates OpenAPI 3.1 | ✅ |
| `GET /api/contents/{contentId}/artifacts/{artifactId}/recommendations` documented | ✅ |
| Path parameter `contentId` (uuid) | ✅ |
| Path parameter `artifactId` (uuid) | ✅ |
| Response 200 → `#/components/schemas/ArtifactRecommendations` | ✅ |
| Response 400 → `ErrorResponse` | ✅ |
| Schema `RecommendedArtifact` with `artifactId`, `type`, `title`, `reason` | ✅ |
| Schema `ArtifactRecommendations` with `recommendations[]` | ✅ |
| Schema `RecommendationReason` enum | ✅ |
| `type` reuses `ArtifactType` enum | ✅ |

**New shared schemas (Presentation only):**

| Schema | File |
| ------ | ---- |
| `RecommendedArtifact` | `backend/src/Presentation/OpenApi/Schema/RecommendedArtifact.php` |
| `ArtifactRecommendations` | `backend/src/Presentation/OpenApi/Schema/ArtifactRecommendations.php` |
| `RecommendationReason` | `backend/src/Presentation/OpenApi/Schema/RecommendationReasonSchema.php` |

Browse locally: `http://localhost:8000/api/docs`

---

# Architecture rules (Sprint 18 additions)

## Backend

| Rule | Scope | Enforced by |
| ---- | ----- | ----------- |
| Recommendation domain purity | `Domain/Recommendation` | `testRecommendationDomainDoesNotDependOnOuterLayersOrFrameworks` |
| Recommendation application isolation | `Application/Recommendation` | `testRecommendationApplicationMayDependOnRecommendationGraphRelationArtifactAndContentDomainOnly` |
| Recommendation presentation boundary | Controller, Response, OpenAPI schemas | `testRecommendationPresentationMayDependOnRecommendationApplicationOnly` |

## Frontend

| Rule | Enforced by |
| ---- | ----------- |
| No `HttpRecommendationRepository` in features | `findFeatureRecommendationTransportViolations` |
| `SeeAlsoRecommendationsPanel` uses `RecommendationService` only | Same guard + panel unit tests |
| `fetch()` centralized in `HttpClient` | `findFetchViolations` |

Documented in `docs/architecture/architecture-rules.md`.

---

# Documentation verification

| Document | Location | Status |
| -------- | -------- | ------ |
| OpenAPI guide | `docs/architecture/openapi.md` | ✅ Recommendations endpoint + schemas |
| Architecture rules | `docs/architecture/architecture-rules.md` | ✅ Recommendation backend + frontend rules |
| Architecture README | `docs/architecture/README.md` | ✅ Updated (Sprint 18 note) |
| Sprint 18 report | `docs/reports/Sprint18-Verification.md` | ✅ This document |

---

# Functional coverage (Sprint 18)

| Capability | Backend | Frontend | OpenAPI |
| ---------- | ------- | -------- | ------- |
| Build recommendations from graph neighbours | ✅ | — | — |
| Expose recommendations JSON projection API | ✅ | — | ✅ |
| Load recommendations via RecommendationService | — | ✅ | — |
| See also panel on Processing page | — | ✅ | — |
| Anchor navigation to related artifact sections | — | ✅ | — |
| Loading / empty / error states | — | ✅ | — |
| Architecture transport guards | ✅ | ✅ | — |

---

# Test summary (delta from Sprint 17)

| Suite | Sprint 17 | Sprint 18 | Delta |
| ----- | --------- | --------- | ----- |
| Backend PHPUnit | 370 | 398 | +28 |
| Backend architecture | 21 | 24 | +3 |
| Backend OpenAPI | 20 | 23 | +3 |
| Frontend Vitest | 280 | 309 | +29 |
| Worker pytest | 127 | 127 | — |

New frontend architecture test: Recommendation transport guard (+1 rule, 12 total architecture tests).

---

# Known limitations

1. **Direct neighbours only** — recommendations include first-hop graph neighbours; no multi-hop traversal or ranking.
2. **Deterministic relations only** — reasons follow `ArtifactRelationResolver` edges; no AI-inferred semantic links.
3. **No relevance scoring** — all neighbours treated equally; no ordering by importance or user context.
4. **`next` / `previous` reserved** — enum documents future sequential navigation; resolver does not emit these types yet.
5. **Per-artifact API calls** — each `SeeAlsoRecommendationsPanel` calls `RecommendationService` independently (same pattern as relations/graph panels).
6. **Mock mode UUID requirement** — `RecommendationService` requires UUID ids; mock Processing page uses non-UUID `content-1` so panels return empty recommendations.
7. **Backend image rebuild required** — backend has no bind mount; code changes need `docker compose build backend && docker compose up -d backend --force-recreate`.
8. **Podcast documented but not generated** — OpenAPI lists `podcast`; worker does not produce it yet.

---

# Recommendations for Sprint 19

Infrastructure is mature (Clean Architecture, CQRS, OpenAPI, architecture tests, CI, worker pipeline, Library, Collections, Search, interactive Timeline, Historical Map, Artifact Relations, Knowledge Graph, Contextual Recommendations). Sprint 19 should **enrich recommendations with relevance scoring**:

| Priority | Feature | Rationale |
| -------- | ------- | --------- |
| 🥇 | **RecommendationScoringEngine** | Order "See also" by relation type, graph proximity, artifact type |
| 🥈 | **Semantic RAG** | RAG leveraging Library, Timeline, Map, Relations, Graph, and Recommendations |
| 🥉 | **User behaviour signals** | Track saved artifacts and quiz completion to weight recommendations |

**Recommended architecture for Sprint 19:**

```text
KnowledgeGraph
        │
        ▼
RecommendationScoringEngine
        │
        ▼
ScoredRecommendations
        │
        ▼
RecommendationService
        │
        ▼
See also (ordered by relevance)
```

This adds a scoring strategy layer without changing the core `RecommendationEngine` or graph domains.

---

# CTO criteria (Sprint 18 closure)

| Criterion | Status |
| --------- | ------ |
| No business logic modified in final slice | ✅ |
| All test suites green | ✅ |
| Architecture tests green | ✅ |
| OpenAPI tests green | ✅ |
| Documentation up to date | ✅ |
| Verification report generated | ✅ |

**Sprint 18 is officially closed.**

---

# Related commits (main branch)

| Commit | Message |
| ------ | ------- |
| `5f54c50` | feat(recommendation): add recommendation domain |
| `e135b69` | feat(recommendation): expose artifact recommendations projection |
| `505f2d1` | fix(frontend): restore api config exports broken in projection commit |
| `cb2af58` | feat(frontend): add artifact recommendation service |
| `7dd06c7` | feat(frontend): add see also recommendations panel |
| `ede134d` | chore(ci): upgrade actions to node 24 compatible versions |
| *(this slice)* | docs(recommendation): document recommendations api and sprint 18 verification |

---

# Sign-off

Verified by: automated suite execution + OpenAPI schema tests

Environment: Docker Compose (backend PHP 8.4, frontend Node 22, worker Python 3.13)

Next sprint: **Sprint 19** — weighted recommendation scoring powered by the knowledge graph
