# Sprint 19 — Final Verification Report

Version: 1.0

Status: Accepted

Date: 2026-06-28

---

# Executive summary

Sprint 19 delivered **relevance scoring for contextual recommendations** end-to-end: scoring domain, API score exposure, frontend score badges, OpenAPI documentation, and architecture verification. Slice 5 changed **documentation and OpenAPI only** — no business logic in backend, frontend, or worker.

| Area | Result |
| ---- | ------ |
| Backend PHPUnit | ✅ 423 tests, 1418 assertions |
| Backend architecture | ✅ 25 tests, 27 assertions |
| Backend OpenAPI | ✅ 23 tests, 247 assertions |
| Frontend build | ✅ OK |
| Frontend Vitest | ✅ 316 tests (68 files) |
| Frontend Biome | ✅ clean (373 files) |
| Worker pytest | ✅ 127 tests |
| Worker Ruff | ✅ All checks passed |
| OpenAPI / Swagger | ✅ `RecommendedArtifact.score` documented (integer 0–100) |

---

# Sprint 19 scope (slices 01–05)

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| S19-SLICE-01 | `RecommendationScoringEngine`, `RecommendationScore`, `RecommendationWeight`, `ScoredRecommendation`, `ScoredRecommendationCollection` | ✅ |
| S19-SLICE-02 | Apply scoring in `GetArtifactRecommendationsHandler` (sorted by score, stable tie-break) | ✅ |
| S19-SLICE-03 | Expose `score` in recommendations JSON API response | ✅ |
| S19-SLICE-04 | Frontend score mapping + `SeeAlsoRecommendationsPanel` relevance badge | ✅ |
| S19-SLICE-05 | OpenAPI `score` field, architecture docs, full verification + this report | ✅ |

---

# Recommendation scoring domain

```text
RecommendedArtifactCollection
        │
        ▼
RecommendationScoringEngine.score()
        │
        ├── Deduplicate by artifactId (first occurrence wins)
        ├── Assign score via RecommendationWeight.forReason(reason)
        └── Sort descending by score; stable tie-break on original index
        │
        ▼
ScoredRecommendationCollection
```

| Type | Role |
| ---- | ---- |
| `RecommendationScore` | Value object (integer 0–100) |
| `RecommendationWeight` | Maps `RecommendationReason` → weight |
| `ScoredRecommendation` | Pairs `RecommendedArtifact` + `RecommendationScore` |
| `RecommendationScoringEngine` | Pure domain scoring; no Symfony or HTTP |

**Score weights (by reason):**

| Reason | Score |
| ------ | ----- |
| `derived_from` | 100 |
| `references` | 80 |
| `related` | 60 |
| `next` | 40 |
| `previous` | 40 |

Engine lives in `backend/src/Domain/Recommendation/` and depends on recommendation domain types only.

---

# API score exposure

```text
KnowledgeGraph
        │
        ▼
RecommendationEngine → RecommendedArtifactCollection
        │
        ▼
RecommendationScoringEngine → ScoredRecommendationCollection
        │
        ▼
GetArtifactRecommendationsHandler
        │
        ▼
GET /api/contents/{contentId}/artifacts/{artifactId}/recommendations
        │
        ▼
{
  "recommendations": [
    { "artifactId", "type", "title", "reason", "score" }
  ]
}
```

- Each recommendation includes integer `score` (0–100).
- Response order reflects backend scoring (descending score); frontend does not re-sort.
- Empty collection when content or artifact is unknown.

---

# Frontend score support

```text
GET /recommendations (score in JSON)
        │
        ▼
HttpRecommendationRepository → mapRecommendedArtifactFromApi()
        │
        ▼
RecommendationService.getArtifactRecommendations()
        │
        ▼
SeeAlsoRecommendationsPanel → formatRecommendationScoreLabel()
        │
        ▼
"80% relevant" badge (omitted when score absent)
```

| Decision | Rationale |
| -------- | --------- |
| Score normalized at API boundary (`types.ts`) | Single 0–100 integer validation |
| Optional `score` on domain type | Backward compatible with legacy responses |
| No frontend sort or score calculation | Backend owns ordering and weights |
| Panel uses `RecommendationService` only | Preserves feature/service boundary |

---

# OpenAPI / Swagger status

Verified via `tests/Functional/OpenApi/ApiDocumentationTest.php`:

| Check | Status |
| ----- | ------ |
| `/api/docs` (Swagger UI) available | ✅ |
| `/api/docs.json` generates OpenAPI 3.1 | ✅ |
| `GET /api/contents/{contentId}/artifacts/{artifactId}/recommendations` documented | ✅ |
| Response 200 → `#/components/schemas/ArtifactRecommendations` | ✅ |
| Schema `RecommendedArtifact` includes `artifactId`, `type`, `title`, `reason`, `score` | ✅ |
| `score` type `integer`, minimum 0, maximum 100, example 80 | ✅ |
| Schema `ArtifactRecommendations` with `recommendations[]` | ✅ |
| Schema `RecommendationReason` enum | ✅ |

Browse locally: `http://localhost:8000/api/docs`

---

# Architecture rules

## Backend (unchanged from Sprint 18)

| Rule | Scope | Enforced by |
| ---- | ----- | ----------- |
| Recommendation domain purity | `Domain/Recommendation` | Architecture tests |
| Recommendation application isolation | `Application/Recommendation` | Architecture tests |
| Recommendation presentation boundary | Controller, Response, OpenAPI | Architecture tests |

## Frontend (unchanged from Sprint 18)

| Rule | Enforced by |
| ---- | ----------- |
| No `HttpRecommendationRepository` in features | `findFeatureRecommendationTransportViolations` |
| `SeeAlsoRecommendationsPanel` uses `RecommendationService` only | Transport guard + panel unit tests |
| `fetch()` centralized in `HttpClient` | `findFetchViolations` |

Documented in `docs/architecture/architecture-rules.md`.

---

# Documentation verification

| Document | Location | Status |
| -------- | -------- | ------ |
| OpenAPI guide | `docs/architecture/openapi.md` | ✅ `score` on recommendations endpoint |
| Architecture README | `docs/architecture/README.md` | ✅ Sprint 19 section |
| Sprint 19 report | `docs/reports/Sprint19-Verification.md` | ✅ This document |

---

# Validation summary

## Backend

```bash
docker compose exec backend php bin/phpunit
```

```
OK (423 tests, 1418 assertions)
```

```bash
docker compose exec backend php bin/phpunit tests/Architecture
```

```
OK (25 tests, 27 assertions)
```

```bash
docker compose exec backend php bin/phpunit tests/Functional/OpenApi/
```

```
OK (23 tests, 247 assertions)
```

> **Note:** The backend image has no bind mount. After OpenAPI annotation changes, run `docker compose build backend && docker compose up -d backend --force-recreate` so the running container serves updated schemas. Workspace validation used bind mount: `docker compose run --rm --no-deps --entrypoint php -v "$(pwd)/backend:/var/www/html" backend bin/phpunit tests/Functional/OpenApi/`.

## Frontend

```bash
docker compose exec frontend npm run build
docker compose exec frontend npm test
docker compose exec frontend npm run check
```

```
npm run build  ✓ built in ~1s
npm test       316 passed (68 files)
npm run check  Checked 373 files. No fixes applied.
```

## Worker

```bash
docker compose exec worker pytest
docker compose exec worker ruff check .
```

```
127 passed, 1 warning in ~2s
All checks passed!
```

*(Exact test counts recorded at verification time in the commit that closes Sprint 19.)*

---

# Known limitations

1. **Reason-based weights only** — scores derive from relation type, not user behaviour or semantic similarity.
2. **Direct neighbours only** — `RecommendationEngine` still recommends first-hop graph neighbours only.
3. **Deterministic relations** — reasons follow `ArtifactRelationResolver` edges; no AI-inferred links.
4. **`next` / `previous` reserved** — enum and weights exist; resolver does not emit these types yet.
5. **Per-artifact API calls** — each `SeeAlsoRecommendationsPanel` calls `RecommendationService` independently.
6. **Mock mode UUID requirement** — non-UUID content ids return empty recommendations in mock mode.
7. **Backend image rebuild required** — code changes need image rebuild without bind mount.
8. **Podcast documented but not generated** — OpenAPI lists `podcast`; worker does not produce it yet.
9. **Optional score in frontend type** — UI tolerates missing `score` for backward compatibility; OpenAPI documents `score` as required on the public contract.

---

# Recommendations for Sprint 20

Infrastructure is mature (Clean Architecture, CQRS, OpenAPI, architecture tests, CI, worker pipeline, Library, Collections, Search, Timeline, Map, Relations, Graph, Scored Recommendations). Sprint 20 should **deepen intelligence and discovery**:

| Priority | Feature | Rationale |
| -------- | ------- | --------- |
| 🥇 | **Semantic RAG** | RAG leveraging Library, Timeline, Map, Relations, Graph, and Recommendations |
| 🥈 | **User behaviour signals** | Track saved artifacts and quiz completion to adjust scores |
| 🥉 | **Multi-hop recommendations** | Extend engine beyond direct graph neighbours |

**Suggested architecture:**

```text
KnowledgeGraph + UserSignals + Embeddings
        │
        ▼
HybridScoringStrategy
        │
        ▼
ScoredRecommendations
        │
        ▼
See also (ordered by blended relevance)
```

---

# CTO criteria (Sprint 19 closure)

| Criterion | Status |
| --------- | ------ |
| OpenAPI documents `score` (integer 0–100) | ✅ |
| No business logic modified in final slice | ✅ |
| All test suites green | ✅ |
| Architecture tests green | ✅ |
| OpenAPI tests green | ✅ |
| Documentation up to date | ✅ |
| Verification report generated | ✅ |

**Sprint 19 is officially closed.**

---

# Related commits (main branch)

| Commit | Message |
| ------ | ------- |
| `98495dc` | feat(recommendation): add recommendation scoring domain |
| `ff12eaf` | feat(recommendation): apply scoring in recommendations api handler |
| `bddbab8` | feat(recommendation): expose recommendation score in api |
| `63341a7` | feat(recommendation): display recommendation score in frontend |
| *(this slice)* | docs(recommendation): document recommendation score in openapi and sprint 19 verification |

---

# Test summary (delta from Sprint 18)

| Suite | Sprint 18 | Sprint 19 | Delta |
| ----- | --------- | --------- | ----- |
| Backend PHPUnit | 398 | 423 | +25 |
| Backend architecture | 24 | 25 | +1 |
| Backend OpenAPI | 241 | 247 | +6 |
| Frontend Vitest | 309 | 316 | +7 |
| Worker pytest | 127 | 127 | — |

---

# Sign-off

Verified by: automated suite execution + OpenAPI schema tests

Environment: Docker Compose (backend PHP 8.4, frontend Node 22, worker Python 3.13)

Next sprint: **Sprint 20** — semantic RAG and hybrid recommendation scoring
