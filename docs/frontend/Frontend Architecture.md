# Frontend Architecture

History AI frontend follows a layered architecture that keeps UI, business logic, and HTTP concerns separate.

## Layers

```text
features/          React screens and feature components
    ↓
services/          Domain services (ContentService, ProcessingService)
    ↓
repositories/      Data access (Mock or HTTP implementations)
    ↓
HttpClient         Single fetch gateway
    ↓
Symfony API
```

### Features

Feature folders (`dashboard`, `library`, `import`, `processing`) contain React components, hooks, and CSS modules. They call **services only** — never repositories, `HttpClient`, or API DTO types.

### Services

Services orchestrate use cases: validation, aggregation, and coordination. Example: `ContentService.getDashboardData()` loads contents and computes statistics.

### Repositories

Repositories abstract data access behind a stable interface (`ContentRepository`). The factory selects mock or HTTP implementation at runtime.

### Shared infrastructure

| Path | Role |
|------|------|
| `src/config/` | Centralized environment and feature flags |
| `src/shared/errors/` | Typed error hierarchy |
| `src/services/http/HttpClient.ts` | Only place that calls `fetch` |
| `src/ui/` | Generic, reusable UI primitives |

## Content domain layout

```text
services/content/
  api/ContentApiDto.ts      Wire format (matches Symfony JSON)
  domain/Content.ts         Frontend domain model
  mappers/ContentMapper.ts  api ↔ domain translation
  ContentService.ts         Use cases
  ContentRepository.ts      Interface
  MockContentRepository.ts
  HttpContentRepository.ts
  ContentRepositoryFactory.ts
```

## Configuration

All `import.meta.env` access lives in `src/config/env.ts`. Other modules import from `@/config`:

- `FEATURES.USE_MOCK` — mock vs real backend
- `API_BASE_URL`, `CONTENTS_PATH` — HTTP endpoints
- `APP` — application metadata

## Error handling

```text
DomainError
  ├── NetworkError    fetch / connectivity failures
  ├── ValidationError client-side validation (e.g. invalid PDF)
  └── ApiError        non-2xx HTTP responses (includes status code)
```

`HttpClient` throws `ApiError` or `NetworkError`. `ContentService` throws `ValidationError`. Features catch errors and show user-facing messages without inspecting HTTP details.

## Rules (PR checklist)

See [PR Quality Checklist](./PR-QUALITY-CHECKLIST.md).

## Related docs

- [Content Flow](./Content%20Flow.md)
- [Repository Pattern](./Repository%20Pattern.md)
