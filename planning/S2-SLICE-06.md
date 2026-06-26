# S2-SLICE-06 — Real Content Flow Review & Cleanup

**Status:** Done  
**Goal:** Consolidate frontend architecture — no new features, no UI/backend/API changes.

## Scope

1. Remove obsolete content helpers (`types.ts`, `apiTypes.ts`, mappers split across files).
2. Standardize errors under `src/shared/errors/`.
3. Standardize DTOs: `api/`, `domain/`, `mappers/`.
4. Centralize config: `src/config/{env,features,app,api}.ts`.
5. Document architecture in `docs/frontend/`.
6. Maintain / improve test coverage for services, repositories, mappers.

## Removed files

- `frontend/src/services/content/types.ts`
- `frontend/src/services/content/apiTypes.ts`
- `frontend/src/services/content/mapContentFromApi.ts`
- `frontend/src/services/content/mapContentFromApi.test.ts`
- `frontend/src/services/content/mapSourceType.ts`
- `frontend/src/services/content/ContentApiError.ts`
- `frontend/src/services/content/deriveTitleFromPdfFileName.ts`
- `frontend/src/services/content/deriveTitleFromPdfFileName.test.ts`

(Obsolete screen services `DashboardService`, `LibraryService`, `ImportService` were removed in S2-SLICE-01.)

## Added / reorganized

```text
frontend/src/shared/errors/     DomainError hierarchy
frontend/src/config/            env, features, app, api
frontend/src/services/content/
  api/ContentApiDto.ts
  domain/Content.ts
  mappers/ContentMapper.ts
docs/frontend/
  Frontend Architecture.md
  Content Flow.md
  Repository Pattern.md
  PR-QUALITY-CHECKLIST.md
```

## Architectural decisions

- **Errors:** `ContentApiError` replaced by shared `ApiError` / `NetworkError` / `ValidationError`.
- **Mapping:** Single `ContentMapper` owns API ↔ domain translation including `sourceType`.
- **Config:** Only `env.ts` reads Vite env vars; features and services import `@/config`.
- **Behavior:** Unchanged — same screens, same API calls, same mock toggle.

## Out of scope

- MinIO, Whisper, Workers, ProcessingJob pipeline
- UI changes
- Backend changes
- API contract changes

## Validation

```bash
cd frontend && npm run check && npm test && npm run build
```

## Next

Sprint 3 — first end-to-end processing workflow (real PDF upload, ProcessingJob, Python worker).
