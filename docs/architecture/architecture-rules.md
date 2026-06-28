# Architecture Rules

Version: 1.0

Status: Active

---

# Purpose

This document defines **enforceable dependency rules** for History AI. Rules are checked automatically by architecture tests in backend, worker, and frontend.

When a rule is violated, the corresponding test suite **fails in CI**.

Related ADRs: [docs/architecture/README.md](./README.md)

---

# Backend (Symfony)

## Layer model

```text
Presentation
      │
      ▼
Application
      │
      ▼
Domain
      ▲
      │
Infrastructure
```

| Layer | May depend on | Must not depend on |
| ----- | ------------- | ------------------ |
| **Domain** | Domain, PHP stdlib | Symfony, Doctrine, Infrastructure, Presentation |
| **Application** | Domain, Application | Infrastructure, Presentation |
| **Presentation** | Domain, Application, Presentation, Symfony | Infrastructure |
| **Infrastructure** | Domain, Application, Infrastructure, Doctrine | Presentation |

Presentation may reference Domain value objects and exceptions when parsing HTTP input (e.g. `ContentId`, `InvalidContentIdException`). Handlers remain the primary orchestration path.

## Enforcement

| Tool | Location | Command |
| ---- | -------- | ------- |
| PHPUnit architecture tests | `backend/tests/Architecture/` | `docker compose exec backend php bin/phpunit tests/Architecture` |
| Deptrac config (reference) | `backend/deptrac.yaml` | Optional: `vendor/bin/deptrac analyse` after installing Deptrac |

### Rules tested

1. **Domain purity** — no `Symfony\`, `Doctrine\`, `App\Infrastructure\`, `App\Presentation\` imports.
2. **Application isolation** — no Infrastructure or Presentation imports.
3. **Presentation boundary** — no direct Infrastructure imports (use Application handlers).
4. **Infrastructure boundary** — no Presentation imports.
5. **Search domain** — `Domain/Search` follows the same purity rules as other domains (no Symfony, Doctrine, Infrastructure, or Presentation).
6. **Search application** — `Application/Search` depends on `Domain/Search` only (via handlers and DTOs).
7. **Search infrastructure** — `Infrastructure/Persistence/Doctrine/Search` may depend on `Domain/Search` and `Domain/Library`; must not import Presentation.
8. **Search presentation** — controllers, requests, and responses under `Presentation/Http/.../Search` may depend on `Application/Search` and Domain value objects; must not import Infrastructure.
9. **Timeline domain** — `Domain/Timeline` follows the same purity rules as other domains (no Symfony, Doctrine, Infrastructure, or Presentation).
10. **Timeline application** — `Application/Timeline` depends on Domain only (via handlers and DTOs); must not import Infrastructure or Presentation.
11. **Timeline presentation** — controllers, responses, and OpenAPI schemas under `Presentation/Http/.../Timeline` and `Presentation/OpenApi/Schema/Timeline*` may depend on `Application/Timeline` and Domain value objects; must not import Infrastructure.
12. **Relation domain** — `Domain/Relation` follows the same purity rules as other domains (no Symfony, Doctrine, Infrastructure, or Presentation).
13. **Relation application** — `Application/Relation` depends on `Domain/Relation`, `Domain/Artifact`, and `Domain/Content` only; must not import Infrastructure or Presentation.
14. **Relation presentation** — controllers, responses, and OpenAPI schemas under `Presentation/Http/.../Relation` and `Presentation/OpenApi/Schema/ArtifactRelation*` may depend on `Application/Relation` and Domain value objects; must not import Infrastructure.

### Search example (passes CI)

```php
// backend/src/Application/Search/Handlers/SearchLibraryHandler.php
use App\Domain\Search\LibrarySearchRepositoryInterface; // ✅ port in Domain

// backend/src/Infrastructure/Persistence/Doctrine/Search/DoctrineLibrarySearchRepository.php
use App\Domain\Search\SearchQuery;
use App\Domain\Library\LibraryItem; // ✅ Search reads library items via Domain
```

### Example violation (fails CI)

```php
// backend/src/Domain/Collection/Collection.php
use Doctrine\ORM\EntityManager; // ❌ forbidden
```

**Fix:** move persistence logic to `Infrastructure/Persistence/Doctrine/` and expose a repository port in Domain.

```php
// backend/src/Application/Collection/Handlers/CreateCollectionHandler.php
use App\Infrastructure\Persistence\Doctrine\Collection\DoctrineCollectionRepository; // ❌ forbidden
```

**Fix:** depend on `CollectionRepositoryInterface` in the handler constructor; wire the Doctrine adapter in Symfony DI config.

---

# Worker (FastAPI)

## Module model

```text
ProcessingService
      │
      ▼
ArtifactGeneratorInterface / SummaryGeneratorInterface
      │
      ▼
AIProviderInterface
      │
      ▼
MockAIProvider | GeminiProvider
```

| Rule | Rationale |
| ---- | --------- |
| `app/generators/` must not import `GeminiProvider` or `MockAIProvider` | Generators depend on abstractions, not vendors |
| `app/generators/` may import `AIProviderInterface` and `AIProviderFactory` | Factory is the composition root for AI wiring |
| `GeminiProvider` references only in `app/ai/` (plus tests) | Concrete providers stay in the AI adapter layer |

Note: the worker has no `app/domain/` package yet; domain logic lives in services and generators. When a domain package is introduced, it must not import `app/ai`.

## Enforcement

| Tool | Location | Command |
| ---- | -------- | ------- |
| pytest architecture tests | `worker/tests/test_architecture.py` | `docker compose exec worker pytest tests/test_architecture.py` |

### Example violation (fails CI)

```python
# worker/app/generators/QuizArtifactGenerator.py
from app.ai.GeminiProvider import GeminiProvider  # ❌ forbidden
```

**Fix:** accept `AIProviderInterface` via constructor injection; resolve the provider in `AIProviderFactory`.

---

# Frontend (React)

## Layer model

```text
features/ (UI)
      │
      ▼
services/ (use cases)
      │
      ▼
HttpClient + Repository (Http / Mock)
```

| Rule | Rationale |
| ---- | --------- |
| `fetch()` only in `services/http/HttpClient.ts` | Single HTTP gateway; testable mocks |
| Feature modules must not import `Http*Repository` | UI talks to services, not transport |
| Feature modules must not import `HttpClient` | Same as above |
| Feature modules must not import Search transport (`HttpSearchRepository`, `SearchRepositoryFactory`, `SearchRepository`) | Library search UI uses `SearchService` only |
| Feature modules must not import Timeline transport (`HttpTimelineRepository`, `TimelineRepositoryFactory`, `TimelineRepository`) | Timeline UI uses `TimelineService` only |
| Feature modules must not import Map transport (`HttpMapRepository`, `MapRepositoryFactory`, `MapRepository`) | Map UI uses `MapService` only |
| Feature modules must not import Relation transport (`HttpRelationRepository`, `RelationRepositoryFactory`, `RelationRepository`) | Relations UI uses `RelationService` only |
| `InteractiveTimeline` must not import services or repositories | Structured timeline rendering is props-only |
| `InteractiveMap` must not import services or repositories | Map rendering is props-only |
| Timeline artifact renderers may import `TimelineService` | Service layer owns HTTP/mock wiring |
| Map panels may import `MapService` | Service layer owns HTTP/mock wiring |
| Relation panels may import `RelationService` | Service layer owns HTTP/mock wiring |

Repository factories and Http repositories live under `services/` and are consumed by service classes only.

### Search service layer

```text
features/library
      │
      ▼
SearchService.searchLibrary()
      │
      ▼
SearchRepositoryFactory → HttpSearchRepository | MockSearchRepository
      │
      ▼
HttpClient (HTTP mode only)
```

### Timeline service layer

```text
features/processing/artifactRenderers/TimelineArtifactRenderer
      │
      ▼
TimelineService.getTimeline()
      │
      ▼
TimelineRepositoryFactory → HttpTimelineRepository | MockTimelineRepository
      │
      ▼
HttpClient (HTTP mode only)
      │
      ▼
InteractiveTimeline (props-only, no services)
```

### Map service layer

```text
features/map/TimelineMapPanel
      │
      ▼
MapService.getTimelineMap()
      │
      ▼
MapRepositoryFactory → HttpMapRepository | MockMapRepository
      │
      ▼
HttpClient (HTTP mode only)
      │
      ▼
InteractiveMap (props-only, no services)
```

Timeline artifact integration:

```text
TimelineArtifactRenderer
        │
        ├── InteractiveTimeline (structured timeline)
        └── TimelineMapPanel (when structured timeline is available)
```

### Relation service layer

```text
features/processing/ArtifactRelationsPanel
      │
      ▼
RelationService.getArtifactRelations()
      │
      ▼
RelationRepositoryFactory → HttpRelationRepository | MockRelationRepository
      │
      ▼
HttpClient (HTTP mode only)
```

Processing page integration:

```text
ProcessingArtifacts
        │
        ├── artifact cards (id="artifact-{type}" anchors)
        └── ArtifactRelationsPanel (contentId + artifacts)
```

## Enforcement

| Tool | Location | Command |
| ---- | -------- | ------- |
| Vitest architecture tests | `frontend/src/architecture/` | Included in `npm test` |

### Example violation (fails CI)

```tsx
// frontend/src/features/processing/ProcessingPage.tsx
const response = await fetch("/api/jobs"); // ❌ forbidden
```

**Fix:** call `processingService.createJob(...)` or the appropriate service method.

```tsx
// frontend/src/features/library/Library.tsx
import { HttpLibraryRepository } from "@/services/library/HttpLibraryRepository"; // ❌ forbidden
```

**Fix:** import `libraryService` from `@/services/library/LibraryService`.

```tsx
// frontend/src/features/library/Library/Library.tsx
import { HttpSearchRepository } from "@/services/search/HttpSearchRepository"; // ❌ forbidden
```

**Fix:** import `searchService` from `@/services/search/SearchService`.

```tsx
// frontend/src/features/processing/artifactRenderers/TimelineArtifactRenderer.tsx
import { HttpTimelineRepository } from "@/services/timeline/HttpTimelineRepository"; // ❌ forbidden
```

**Fix:** import `timelineService` from `@/services/timeline/TimelineService`.

```tsx
// frontend/src/features/processing/InteractiveTimeline/InteractiveTimeline.tsx
import { timelineService } from "@/services/timeline/TimelineService"; // ❌ forbidden
```

**Fix:** receive `Timeline` data via props from the parent renderer.

```tsx
// frontend/src/features/map/TimelineMapPanel/TimelineMapPanel.tsx
import { HttpMapRepository } from "@/services/map/HttpMapRepository"; // ❌ forbidden
```

**Fix:** import `mapService` from `@/services/map/MapService`.

```tsx
// frontend/src/features/map/InteractiveMap/InteractiveMap.tsx
import { mapService } from "@/services/map/MapService"; // ❌ forbidden
```

**Fix:** receive place data via props from `TimelineMapPanel`.

```tsx
// frontend/src/features/processing/ArtifactRelationsPanel/ArtifactRelationsPanel.tsx
import { HttpRelationRepository } from "@/services/relation/HttpRelationRepository"; // ❌ forbidden
```

**Fix:** import `relationService` from `@/services/relation/RelationService`.

---

# Running all architecture checks

```bash
# Backend
docker compose exec backend php bin/phpunit tests/Architecture

# Frontend (included in full suite)
docker compose exec frontend npm test

# Worker
docker compose exec worker pytest tests/test_architecture.py
```

Full regression (architecture + business tests):

```bash
docker compose exec backend php bin/phpunit
docker compose exec frontend npm test
docker compose exec worker pytest
```

---

# Adding a new rule

1. Document the rule in this file (context, decision, fix).
2. Add an automated check in the appropriate `tests/Architecture`, `test_architecture.py`, or `src/architecture/` suite.
3. Verify the suite fails with a deliberate violation, then revert.
4. Link to a new ADR if the rule reflects a major architectural decision.

---

# CI integration (Sprint 11 roadmap)

Architecture tests are designed to run in GitHub Actions alongside existing PHPUnit, Vitest, and pytest jobs. No separate business-logic changes are required — only fail-fast guardrails when dependency boundaries are broken.
