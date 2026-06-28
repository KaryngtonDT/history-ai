# Sprint 17 — Final Verification Report

Version: 1.0

Status: Accepted

Date: 2026-06-28

---

# Executive summary

Sprint 17 delivered the **Knowledge Graph** end-to-end: graph projection domain, JSON graph API, frontend `GraphService`, `KnowledgeGraphPanel`, CSS-only `InteractiveGraph`, OpenAPI documentation, and architecture rules. Slice 5 changed **documentation and verification only** — no business logic in backend, frontend, or worker.

| Area | Result |
| ---- | ------ |
| Backend PHPUnit | ✅ 370 tests, 1218 assertions |
| Backend architecture | ✅ 21 tests |
| Backend OpenAPI | ✅ 20 tests |
| Frontend build | ✅ OK |
| Frontend Vitest | ✅ 280 tests |
| Frontend Biome | ✅ clean (356 files) |
| Worker pytest | ✅ 127 tests |
| Worker Ruff | ✅ All checks passed |
| OpenAPI / Swagger | ✅ `GET /api/contents/{contentId}/graph` documented |

---

# Sprint 17 scope (slices 01–05)

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| S17-SLICE-01 | `GraphNode`, `GraphEdge`, `KnowledgeGraph`, `KnowledgeGraphBuilder` domain | ✅ |
| S17-SLICE-02 | `GET /api/contents/{contentId}/graph` JSON projection API | ✅ |
| S17-SLICE-03 | Frontend `GraphService` + Http/Mock repositories | ✅ |
| S17-SLICE-04 | `KnowledgeGraphPanel` + `InteractiveGraph` on Processing page | ✅ |
| S17-SLICE-05 | OpenAPI schemas, architecture docs, full verification + this report | ✅ |

---

# Executed commands and results

## Backend

```bash
docker compose run --rm --no-deps --entrypoint php \
  -v "$(pwd)/backend:/var/www/html" backend php bin/phpunit
```

```
OK (370 tests, 1218 assertions)
Time: ~47s
```

```bash
docker compose run --rm --no-deps --entrypoint php \
  -v "$(pwd)/backend:/var/www/html" backend php bin/phpunit tests/Architecture
```

```
OK (21 tests, 22 assertions)
Time: ~4s
```

```bash
docker compose run --rm --no-deps --entrypoint php \
  -v "$(pwd)/backend:/var/www/html" backend php bin/phpunit tests/Functional/OpenApi/
```

```
OK (20 tests, 197 assertions)
Time: ~80s
```

> **Note:** The backend image has no bind mount. After slice 5 changes, run `docker compose build backend && docker compose up -d backend --force-recreate` so the running container serves updated OpenAPI annotations. Validation above used a one-off bind mount to verify workspace code.

## Frontend

```bash
docker compose exec frontend npm run build
```

```
✓ built in ~13s
dist/assets/index-*.js   ~323 kB │ gzip: ~98 kB
```

```bash
docker compose exec frontend npm test
```

```
Test Files  62 passed (62)
Tests       280 passed (280)
Duration    ~47s
```

```bash
docker compose exec frontend npm run check
```

```
Checked 356 files. No fixes applied.
```

## Worker

```bash
docker compose exec worker pytest
```

```
127 passed, 1 warning in ~2.8s
```

```bash
docker compose exec worker ruff check .
```

```
All checks passed!
```

---

# Knowledge Graph projection

```text
GraphNode
  ├── artifactId (ArtifactId)
  ├── type (ArtifactType)
  └── title (string)

GraphEdge
  ├── sourceArtifactId (ArtifactId)
  ├── targetArtifactId (ArtifactId)
  └── type (ArtifactRelationType)

KnowledgeGraph
  ├── nodes[]
  └── edges[]
```

| Layer | Backend | Frontend |
| ----- | ------- | -------- |
| Domain | `backend/src/Domain/Graph/` | Consumed via API JSON |
| Purity | No Symfony, Doctrine, Infrastructure, Presentation | Panel uses `GraphService` only |

---

# KnowledgeGraphBuilder

```text
Content artifacts (from ArtifactRepository)
        │
        ▼
ArtifactRelationResolver → ArtifactRelationCollection
        │
        ▼
KnowledgeGraphBuilder.build()
        │
        ├── One node per artifact (deduplicated)
        ├── Edges from resolved relations (endpoints must exist)
        └── Empty graph when no artifacts
        │
        ▼
KnowledgeGraph
```

Builder lives in `Domain/Graph` and depends on `Domain/Artifact` and `Domain/Relation` only.

---

# Graph JSON projection API

```text
Content
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
GET /api/contents/{contentId}/graph
        │
        ▼
GetKnowledgeGraphHandler
        │
        ▼
{ "nodes": [...], "edges": [...] }
```

Responses: **200** knowledge graph projection, **400** invalid UUID.

---

# Frontend GraphService

```text
ProcessingArtifacts
        │
        ├── artifact cards (id="artifact-{type}" anchors)
        ├── ArtifactRelationsPanel
        └── KnowledgeGraphPanel
                │
                ▼
        GraphService.getKnowledgeGraph(contentId)
                │
                ▼
        GraphRepositoryFactory → HttpGraphRepository | MockGraphRepository
                │
                ▼
        HttpClient (HTTP mode only)
```

Architecture rules enforced:

- Features may import `GraphService` only (not `HttpGraphRepository`).
- Invalid UUID contentId → empty graph (graceful degradation).
- Mock Processing tests use non-UUID `content-1` → panel shows empty graph (expected).

---

# KnowledgeGraphPanel & InteractiveGraph

| Component | Role |
| --------- | ---- |
| `KnowledgeGraphPanel` | Fetches graph via `GraphService`; loading/empty/error states |
| `InteractiveGraph` | Props-only; CSS layout for nodes and edges; semantic markup |
| `graphLabels.ts` | Human-readable artifact and edge labels; anchor helpers |

Integrated in `ProcessingArtifacts` below `ArtifactRelationsPanel`. `ProcessingArtifacts` still performs a single `artifactService.listByContentId()` call.

---

# OpenAPI / Swagger status

Verified via `tests/Functional/OpenApi/ApiDocumentationTest.php`:

| Check | Status |
| ----- | ------ |
| `/api/docs` (Swagger UI) available | ✅ |
| `/api/docs.json` generates OpenAPI 3.1 | ✅ |
| `GET /api/contents/{contentId}/graph` documented | ✅ |
| Path parameter `contentId` (uuid) | ✅ |
| Response 200 → `#/components/schemas/KnowledgeGraph` | ✅ |
| Response 400 → `ErrorResponse` | ✅ |
| Schema `GraphNode` with `artifactId`, `type`, `title` | ✅ |
| Schema `GraphEdge` with `sourceArtifactId`, `targetArtifactId`, `type` | ✅ |
| Schema `KnowledgeGraph` with `nodes[]`, `edges[]` | ✅ |
| Edge `type` reuses `ArtifactRelationType` enum | ✅ |
| Node `type` reuses `ArtifactType` enum | ✅ |

**New shared schemas (Presentation only):**

| Schema | File |
| ------ | ---- |
| `GraphNode` | `backend/src/Presentation/OpenApi/Schema/GraphNode.php` |
| `GraphEdge` | `backend/src/Presentation/OpenApi/Schema/GraphEdge.php` |
| `KnowledgeGraph` | `backend/src/Presentation/OpenApi/Schema/KnowledgeGraph.php` |

Browse locally: `http://localhost:8000/api/docs`

---

# Architecture rules (Sprint 17 additions)

## Backend

| Rule | Scope | Enforced by |
| ---- | ----- | ----------- |
| Graph domain purity | `Domain/Graph` | `testGraphDomainDoesNotDependOnOuterLayersOrFrameworks` |
| Graph application isolation | `Application/Graph` | `testGraphApplicationMayDependOnGraphRelationArtifactAndContentDomainOnly` |
| Graph presentation boundary | Controller, Response, OpenAPI schemas | `testGraphPresentationMayDependOnGraphApplicationOnly` |

## Frontend

| Rule | Enforced by |
| ---- | ----------- |
| No `HttpGraphRepository` in features | `findFeatureGraphTransportViolations` |
| `InteractiveGraph` props-only (no services) | `findInteractiveGraphServiceViolations` |
| `fetch()` centralized in `HttpClient` | `findFetchViolations` |

Documented in `docs/architecture/architecture-rules.md`.

---

# Documentation verification

| Document | Location | Status |
| -------- | -------- | ------ |
| OpenAPI guide | `docs/architecture/openapi.md` | ✅ Graph endpoint + schemas |
| Architecture rules | `docs/architecture/architecture-rules.md` | ✅ Graph backend + frontend rules |
| Architecture README | `docs/architecture/README.md` | ✅ Updated (Sprint 17 note) |
| Sprint 17 report | `docs/reports/Sprint17-Verification.md` | ✅ This document |

---

# Functional coverage (Sprint 17)

| Capability | Backend | Frontend | OpenAPI |
| ---------- | ------- | -------- | ------- |
| Build knowledge graph from artifacts + relations | ✅ | — | — |
| Expose graph JSON projection API | ✅ | — | ✅ |
| Load graph via GraphService | — | ✅ | — |
| Graph panel on Processing page | — | ✅ | — |
| CSS-only interactive graph view | — | ✅ | — |
| Anchor navigation to artifact sections | — | ✅ | — |
| Loading / empty / error states | — | ✅ | — |
| Architecture transport guards | ✅ | ✅ | — |

---

# Test summary (delta from Sprint 16)

| Suite | Sprint 16 | Sprint 17 | Delta |
| ----- | --------- | --------- | ----- |
| Backend PHPUnit | 344 | 370 | +26 |
| Backend architecture | 18 | 21 | +3 |
| Backend OpenAPI | 17 | 20 | +3 |
| Frontend Vitest | 253 | 280 | +27 |
| Worker pytest | 127 | 127 | — |

New frontend architecture tests: Graph transport guard + InteractiveGraph props-only guard (+2 rules, 11 total architecture tests).

---

# Known limitations

1. **CSS-only graph layout** — no force-directed or canvas graph library; nodes and edges render as ordered lists with a decorative canvas strip.
2. **Deterministic relations only** — graph edges follow `ArtifactRelationResolver` rules; no AI-inferred semantic links.
3. **`next` / `previous` reserved** — enum documents future sequential navigation; resolver does not emit these types yet.
4. **Mock mode UUID requirement** — `GraphService` requires UUID `contentId`; mock Processing page uses `content-1` so panel shows empty graph.
5. **Separate graph fetch** — graph loads via `GraphService` independently from the single artifact list fetch (same pattern as relations).
6. **Backend image rebuild required** — backend has no bind mount; code changes need `docker compose build backend && docker compose up -d backend --force-recreate`.
7. **Podcast documented but not generated** — OpenAPI lists `podcast`; worker does not produce it yet.
8. **React `act(...)` warnings** — non-blocking stderr in Processing/Import page tests (pre-existing).

---

# Recommendations for Sprint 18

Infrastructure is mature (Clean Architecture, CQRS, OpenAPI, architecture tests, CI, worker pipeline, Library, Collections, Search, interactive Timeline, Historical Map, Artifact Relations, Knowledge Graph). Sprint 18 should **exploit the knowledge graph for user value**:

| Priority | Feature | Rationale |
| -------- | ------- | --------- |
| 🥇 | **Contextual Recommendations** | "See also" panel powered by graph edges |
| 🥈 | **Semantic RAG** | RAG leveraging Library, Timeline, Map, Relations, and Graph together |
| 🥉 | **Rich graph visualization** | Optional graph library (e.g. force layout) behind props-only boundary |

**Recommended architecture for Sprint 18:**

```text
KnowledgeGraph
        │
        ▼
RecommendationEngine
        │
        ▼
RecommendedArtifacts
        │
        ▼
RecommendationService
        │
        ▼
"See also" Panel
```

This reuses Sprint 17 graph infrastructure without rework.

---

# CTO criteria (Sprint 17 closure)

| Criterion | Status |
| --------- | ------ |
| No business logic modified in final slice | ✅ |
| All test suites green | ✅ |
| Architecture tests green | ✅ |
| OpenAPI tests green | ✅ |
| Documentation up to date | ✅ |
| Verification report generated | ✅ |

**Sprint 17 is officially closed.**

---

# Related commits (main branch)

| Commit | Message |
| ------ | ------- |
| `83e3739` | feat(graph): add knowledge graph projection domain |
| `365c81f` | feat(graph): expose knowledge graph json projection |
| `5f86650` | feat(frontend): add knowledge graph service |
| `ccff8e7` | feat(frontend): add interactive knowledge graph panel |
| *(this slice)* | docs(graph): document graph api and sprint 17 verification |

---

# Sign-off

Verified by: automated suite execution + OpenAPI schema tests

Environment: Docker Compose (backend PHP 8.4, frontend Node 22, worker Python 3.13)

Next sprint: **Sprint 18** — contextual recommendations powered by the knowledge graph
