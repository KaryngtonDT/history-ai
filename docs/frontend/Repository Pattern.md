# Repository Pattern

Repositories isolate data access so services and UI stay independent of HTTP and mock details.

## Interface

`ContentRepository` defines the contract:

```typescript
listContents(): Promise<Content[]>
createContent(input: CreateContentInput): Promise<CreateContentResult>
```

Services depend on the interface, not concrete classes.

## Implementations

### MockContentRepository

- Used when `FEATURES.USE_MOCK` is true (local dev default in Vitest, optional in Vite).
- Reads/writes in-memory data seeded from `src/mock/content.ts`.
- Supports deterministic tests without a running backend.

### HttpContentRepository

- Used when `FEATURES.USE_MOCK` is false (Docker production build).
- Delegates to `HttpClient` for `GET` and `POST`.
- Maps API DTOs through `ContentMapper` before returning domain models.

## Factory

`ContentRepositoryFactory.createContentRepository()` selects the implementation:

```typescript
if (FEATURES.USE_MOCK) {
  return new MockContentRepository();
}
return new HttpContentRepository(new HttpClient(API_BASE_URL));
```

`ContentService` receives the repository via constructor; the exported singleton uses the factory once at module load.

## Adding a new domain

When a new bounded context appears (e.g. processing jobs):

1. Define `domain/` types for UI-facing models.
2. Define `api/` DTOs matching Symfony JSON.
3. Add `mappers/` for translation.
4. Create `XxxRepository` interface + Mock + Http implementations.
5. Add `XxxRepositoryFactory` using `FEATURES` and `HttpClient`.
6. Expose use cases through `XxxService` — features import the service only.

## Testing

| Layer | Approach |
|-------|----------|
| Mapper | Pure functions — 100% unit coverage |
| Repository | Mock `HttpClient`; assert paths and mapping |
| Service | Inject mock repository; assert orchestration |
| Features | Component tests with mocked service where needed |

Repositories are never imported from React components or tested through the DOM.
