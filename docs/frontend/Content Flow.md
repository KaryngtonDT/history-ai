# Content Flow

How content moves through the frontend from user action to API and back.

## Dashboard

```text
Dashboard.tsx
  → contentService.getDashboardData()
    → ContentRepository.listContents()
      → GET /api/contents
    → computeStatistics(contents)
  → RecentContents + Statistics
```

The dashboard reuses the same list endpoint as the library. Statistics are derived client-side until dedicated API endpoints exist.

## Library

```text
Library.tsx
  → contentService.listContents()
    → ContentRepository.listContents()
      → GET /api/contents
  → LibraryContentList → LibraryContentCard
```

Empty and error states use shared `EmptyState`. Loading uses `Spinner`.

## Import (PDF)

```text
Import.tsx
  → contentService.validatePdf(file)
  → contentService.simulateUpload()     UI progress only (no bytes sent)
  → contentService.importPdf(file)
    → deriveTitleFromPdfFileName(name)
    → ContentRepository.createContent()
      → POST /api/contents { title, sourceType: "upload_pdf" }
  → UploadSuccess (shows created content id)
```

File upload to object storage is **not** implemented yet. Import creates a content record via JSON POST.

## Mapping boundary

API responses never reach React components directly.

```text
JSON response
  → ContentApiDto (api/)
  → ContentMapper.fromApi()
  → Content (domain/)
  → feature components
```

`sourceType` is normalized: API `upload_pdf` ↔ domain `pdf`. See `planning/TECH-DEBT-sourceType-alignment.md`.

## Mock vs real backend

| `VITE_USE_MOCK` | Repository | Data source |
|-----------------|------------|-------------|
| `true` | `MockContentRepository` | `src/mock/content.ts` |
| `false` | `HttpContentRepository` | Symfony `GET/POST /api/contents` |

Controlled by `FEATURES.USE_MOCK` from `src/config/features.ts`.

## Error paths

| Step | Error type | User message (example) |
|------|------------|------------------------|
| Invalid file type | `ValidationError` | Only PDF files are supported |
| Network offline | `NetworkError` | Unable to load library |
| HTTP 4xx/5xx | `ApiError` | Upload failed |

Features map `DomainError` subclasses to copy; they do not branch on HTTP status codes in JSX.
