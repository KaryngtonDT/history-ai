# Sprint 15 — Final Verification Report

Version: 1.0

Status: Accepted

Date: 2026-06-26

---

# Executive summary

Sprint 15 delivered the **interactive Historical Map** end-to-end: `HistoricalPlace` domain model, `TimelinePlaceResolver`, JSON map projection API, frontend `MapService`, `TimelineMapPanel` + `InteractiveMap` UI, OpenAPI documentation, and architecture rules. Slice 6 changed **documentation and verification only** — no business logic in backend, frontend, or worker.

| Area | Result |
| ---- | ------ |
| Backend PHPUnit | ✅ 315 tests, 1012 assertions |
| Backend architecture | ✅ 15 tests |
| Backend OpenAPI | ✅ 14 tests |
| Frontend build | ✅ OK |
| Frontend Vitest | ✅ 228 tests |
| Frontend Biome | ✅ clean |
| Worker pytest | ✅ 127 tests |
| Worker Ruff | ✅ All checks passed |
| OpenAPI / Swagger | ✅ `GET /api/maps/timeline/{artifactId}` documented |

---

# Sprint 15 scope (slices 01–06)

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| S15-SLICE-01 | `HistoricalPlace`, `Coordinates`, `HistoricalPlaceCollection` domain | ✅ |
| S15-SLICE-02 | `TimelinePlaceResolver` — deterministic place extraction from timeline events | ✅ |
| S15-SLICE-03 | `GET /api/maps/timeline/{artifactId}` JSON projection API | ✅ |
| S15-SLICE-04 | Frontend `MapService` + Http/Mock repositories | ✅ |
| S15-SLICE-05 | `InteractiveMap`, `TimelineMapPanel`, timeline renderer integration | ✅ |
| S15-SLICE-06 | OpenAPI schemas, architecture docs, full verification + this report | ✅ |

---

# Executed commands and results

## Backend

```bash
docker compose exec backend php bin/phpunit
```

```
OK (315 tests, 1012 assertions)
Time: ~14s
```

```bash
docker compose exec backend php bin/phpunit tests/Architecture
```

```
OK (15 tests, 16 assertions)
Time: ~3s
```

```bash
docker compose exec backend php bin/phpunit tests/Functional/OpenApi/
```

```
OK (14 tests, 119 assertions)
Time: ~9s
```

## Frontend

```bash
docker compose exec frontend npm run build
```

```
✓ built in ~13s
dist/assets/index-*.js   ~313 kB │ gzip: ~96 kB
```

```bash
docker compose exec frontend npm test
```

```
Test Files  51 passed (51)
Tests       228 passed (228)
Duration    ~41s
```

```bash
docker compose exec frontend npm run check
```

```
Checked 322 files. No fixes applied.
```

## Worker

```bash
docker compose exec worker pytest
```

```
127 passed, 1 warning in ~1.3s
```

```bash
docker compose exec worker ruff check .
```

```
All checks passed!
```

---

# Historical Place domain

```text
HistoricalPlace
  ├── name (PlaceName)
  ├── coordinates (Coordinates: latitude, longitude)
  └── description? (event text where place was matched)

HistoricalPlaceCollection
  └── places[]

Coordinates
  ├── latitude
  └── longitude
```

| Layer | Backend | Frontend |
| ----- | ------- | -------- |
| Domain | `backend/src/Domain/Map/` | Consumed via API JSON |
| Purity | No Symfony, Doctrine, Infrastructure, Presentation | Map UI receives typed props |

Known seed coordinates (deterministic resolver): Rome, Carthage, Athens, Alexandria.

---

# TimelinePlaceResolver

```text
Timeline (markdown → parsed)
        │
        ▼
TimelinePlaceResolver.resolve()
        │
        ├── Scan section events for known place names
        ├── Deduplicate by place name
        └── Attach event text as optional description
        │
        ▼
HistoricalPlaceCollection
```

Resolver lives in `Domain/Map` and depends only on `Domain/Timeline`. No HTTP, no persistence, no framework imports.

---

# Timeline → Map projection

```text
Timeline artifact (markdown)
        │
        ▼
TimelineParser → Timeline
        │
        ▼
TimelinePlaceResolver → HistoricalPlaceCollection
        │
        ▼
GET /api/maps/timeline/{artifactId}
        │
        ▼
GetTimelineMapHandler
        │
        ▼
TimelineMapResponse { places[].name, coordinates, description? }
```

Responses: **200** map projection, **400** invalid UUID, **404** timeline artifact not found.

---

# Map API

| Method | Path | Response |
| ------ | ---- | -------- |
| GET | `/api/maps/timeline/{artifactId}` | `Map` JSON with `places[]` |

Controller: `GetTimelineMapController`  
Handler: `GetTimelineMapHandler`  
OpenAPI operationId: `getTimelineMap`

---

# Frontend MapService

```text
TimelineArtifactRenderer
        │
        ├── InteractiveTimeline (structured timeline)
        └── TimelineMapPanel (when timeline JSON available)
                │
                ▼
        MapService.getTimelineMap(artifactId)
                │
                ▼
        MapRepositoryFactory → HttpMapRepository | MockMapRepository
                │
                ▼
        HttpClient (HTTP mode only)
                │
                ▼
        InteractiveMap (props-only, CSS layout)
```

Architecture rules enforced:

- Features may import `MapService` only (not `HttpMapRepository`).
- `InteractiveMap` is props-only — no service imports.
- `TimelineMapPanel` loads map data and passes places to `InteractiveMap`.

---

# Interactive Map UI

| Component | Role |
| --------- | ---- |
| `TimelineMapPanel` | Fetches map via `MapService`, loading/error states |
| `InteractiveMap` | Renders place markers on a CSS-only map canvas (no Leaflet/MapLibre) |

Integrated below structured timeline in `TimelineArtifactRenderer` when JSON timeline loads successfully.

---

# OpenAPI / Swagger status

Verified via `tests/Functional/OpenApi/ApiDocumentationTest.php`:

| Check | Status |
| ----- | ------ |
| `/api/docs` (Swagger UI) available | ✅ |
| `/api/docs.json` generates OpenAPI 3.1 | ✅ |
| `GET /api/maps/timeline/{artifactId}` documented | ✅ |
| Path parameter `artifactId` (uuid) | ✅ |
| Response 200 → `#/components/schemas/Map` | ✅ |
| Response 400 → `ErrorResponse` | ✅ |
| Response 404 → `ErrorResponse` | ✅ |
| Schema `Map` with `places[]` | ✅ |
| Schema `HistoricalPlace` with `name`, `coordinates`, optional `description` | ✅ |
| Schema `Coordinates` with `latitude`, `longitude` | ✅ |

**New shared schemas (Presentation only):**

| Schema | File |
| ------ | ---- |
| `Map` | `backend/src/Presentation/OpenApi/Schema/Map.php` |
| `HistoricalPlace` | `backend/src/Presentation/OpenApi/Schema/HistoricalPlace.php` |
| `Coordinates` | `backend/src/Presentation/OpenApi/Schema/Coordinates.php` |

**Nelmio registration note:** the `Map` alias must be quoted in YAML (`'Map'`) because unquoted `Map` is parsed as a YAML mapping key and entries are silently dropped.

Browse locally: `http://localhost:8000/api/docs`

---

# Architecture rules (Sprint 15 additions)

## Backend

| Rule | Scope | Enforced by |
| ---- | ----- | ----------- |
| Map domain purity | `Domain/Map` | `testMapDomainDoesNotDependOnOuterLayersOrFrameworks` |
| Map application isolation | `Application/Map` | `testMapApplicationMayDependOnMapAndTimelineDomainOnly` |
| Map presentation boundary | Controller, Response, OpenAPI schemas | `testMapPresentationMayDependOnMapApplicationOnly` |

## Frontend

| Rule | Enforced by |
| ---- | ----------- |
| No `HttpMapRepository` in features | `findFeatureMapTransportViolations` |
| `InteractiveMap` props-only | `findInteractiveMapServiceViolations` |
| `fetch()` centralized in `HttpClient` | `findFetchViolations` |

Documented in `docs/architecture/architecture-rules.md`.

---

# Documentation verification

| Document | Location | Status |
| -------- | -------- | ------ |
| OpenAPI guide | `docs/architecture/openapi.md` | ✅ Map endpoint + schemas |
| Architecture rules | `docs/architecture/architecture-rules.md` | ✅ Map backend + frontend rules |
| Architecture README | `docs/architecture/README.md` | ✅ Updated (Sprint 15 note) |
| Sprint 15 report | `docs/reports/Sprint15-Verification.md` | ✅ This document |

---

# Functional coverage (Sprint 15)

| Capability | Backend | Frontend | OpenAPI |
| ---------- | ------- | -------- | ------- |
| Resolve places from timeline events | ✅ | — | — |
| Expose map JSON projection API | ✅ | — | ✅ |
| Load map via MapService | — | ✅ | — |
| Interactive place markers on map panel | — | ✅ | — |
| Map panel below structured timeline | — | ✅ | — |
| Loading state while fetching map | — | ✅ | — |
| Architecture transport guards | ✅ | ✅ | — |

---

# Test summary (delta from Sprint 14)

| Suite | Sprint 14 | Sprint 15 | Delta |
| ----- | --------- | --------- | ----- |
| Backend PHPUnit | 272 | 315 | +43 |
| Backend architecture | 12 | 15 | +3 |
| Backend OpenAPI | 11 | 14 | +3 |
| Frontend Vitest | 199 | 228 | +29 |
| Worker pytest | 127 | 127 | — |

New frontend architecture tests: Map transport + InteractiveMap props-only (+2 rules, 8 total architecture tests).

---

# Known limitations

1. **Deterministic place catalog** — only Rome, Carthage, Athens, and Alexandria are resolved; no geocoding or AI extraction yet.
2. **CSS-only map** — no pan/zoom, tile layers, or real geographic projection; layout is illustrative.
3. **Map requires structured timeline** — panel appears only when timeline JSON loads; markdown-only fallback shows timeline without map.
4. **No cross-artifact links** — Timeline, Summary, Quiz, Flashcards, and Map remain independent views.
5. **Backend image rebuild required** — backend has no bind mount; code changes need `docker compose build backend && docker compose up -d backend --force-recreate`.
6. **Podcast documented but not generated** — OpenAPI lists `podcast`; worker does not produce it yet.
7. **React `act(...)` warnings** — non-blocking stderr in Processing/Import page tests (pre-existing).

---

# Recommendations for Sprint 16

Infrastructure is mature (Clean Architecture, CQRS, OpenAPI, architecture tests, CI, worker pipeline, Library, Collections, Search, interactive Timeline, Historical Map). Sprint 16 should prioritize **cross-artifact relations**:

```text
Summary
     │
     ├────────────┐
     ▼            ▼
Timeline      Flashcards
     │            │
     └──────┬─────┘
            ▼
          Quiz
            │
            ▼
Historical Map
```

| Priority | Feature | Rationale |
| -------- | ------- | --------- |
| 🥇 | **Artifact relations** | Navigate between artifacts from the same content; high product value |
| 🥈 | **Expanded place resolution** | Geocoding or AI-assisted place extraction beyond seed catalog |
| 🥉 | **Real map library** | Leaflet/MapLibre with pan/zoom when relations layer is stable |

**Recommendation:** Start with **artifact relations** — reuses all architecture from Sprints 10–15 without rework.

---

# CTO criteria (Sprint 15 closure)

| Criterion | Status |
| --------- | ------ |
| No business logic modified in final slice | ✅ |
| All test suites green | ✅ |
| Architecture tests green | ✅ |
| OpenAPI tests green | ✅ |
| Documentation up to date | ✅ |
| Verification report generated | ✅ |

**Sprint 15 is officially closed.**

---

# Related commits (main branch)

| Commit | Message |
| ------ | ------- |
| `9e326c1` | feat(map): add historical place domain |
| `36b5fdd` | feat(map): resolve timeline places deterministically |
| `19baa06` | feat(map): expose timeline map projection |
| `bfe17e2` | feat(frontend): add timeline map service |
| *(pending)* | feat(frontend): add interactive timeline map panel |
| *(pending)* | docs(map): document map api and sprint 15 verification |

---

# Sign-off

Verified by: automated suite execution + OpenAPI schema tests

Environment: Docker Compose (backend PHP 8.4, frontend Node 22, worker Python 3.13)

Next sprint: **Sprint 16** — artifact relations and cross-navigation
