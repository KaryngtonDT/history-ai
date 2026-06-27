# OpenAPI Documentation

Version: 1.0

Status: Active

---

# Purpose

History AI exposes a **machine-readable OpenAPI 3.1** specification for the public REST API. Interactive documentation is served via **Swagger UI** at `/api/docs`.

Internal routes (`/api/processing/*`, `/api/internal/*`) are intentionally excluded.

---

# How documentation is generated

```text
Presentation controllers (#[OA\Post], #[OA\Get], …)
        │
        ▼
NelmioApiDocBundle + swagger-php
        │
        ├── /api/docs       → Swagger UI (HTML)
        └── /api/docs.json  → OpenAPI 3.1 JSON
```

| Component | Location |
| --------- | -------- |
| Bundle | `nelmio/api-doc-bundle` |
| Global metadata | `backend/src/Presentation/OpenApi/OpenApiSpec.php` |
| Shared schemas | `backend/src/Presentation/OpenApi/Schema/` |
| Route annotations | `backend/src/Presentation/Http/Controller/` |
| Nelmio config | `backend/config/packages/nelmio_api_doc.yaml` |
| Doc routes | `backend/config/routes/nelmio_api_doc.yaml` |

The **`default`** area uses `disable_default_routes: true`, so only controller actions annotated with OpenAPI attributes (`#[OA\Post]`, `#[OA\Get]`, …) appear in the spec. Processing and internal endpoints stay excluded without a deny list.

---

# Documented endpoints

| Tag | Method | Path |
| --- | ------ | ---- |
| Contents | POST | `/api/contents` |
| Contents | GET | `/api/contents` |
| Artifacts | GET | `/api/contents/{contentId}/artifacts` |
| Library | POST | `/api/library/items` |
| Library | GET | `/api/library/items` |
| Collections | POST | `/api/collections` |
| Collections | GET | `/api/collections` |
| Collections | POST | `/api/collections/{collectionId}/items` |

---

# Updating annotations

1. Open the controller in `backend/src/Presentation/Http/Controller/`.
2. Add or edit `OpenApi\Attributes` on the action method (before the `#[Route]` attribute).
3. Reuse `#/components/schemas/ErrorResponse` for standard 400 responses.
4. For shared response shapes used in multiple places, add a class under `Presentation/OpenApi/Schema/` with `#[OA\Schema(schema: '…')]`.
5. Run tests (see below) and open `/api/docs` to verify.

Example:

```php
#[OA\Post(
    operationId: 'createContent',
    summary: 'Create content',
    tags: ['Contents'],
    // requestBody, responses …
)]
#[Route('/api/contents', methods: ['POST'])]
public function __invoke(/* … */): JsonResponse
```

Do **not** add OpenAPI attributes to Domain or Application layers — documentation belongs in Presentation only.

---

# Local commands

Start the stack:

```bash
docker compose up -d
```

Browse Swagger UI:

```text
http://localhost:8000/api/docs
```

Fetch raw JSON spec:

```bash
curl -s http://localhost:8000/api/docs.json | jq .openapi
```

Run documentation tests:

```bash
docker compose exec backend php bin/phpunit tests/Functional/OpenApi/
```

Full backend suite:

```bash
docker compose exec backend php bin/phpunit
```

---

# Production considerations

| Topic | Recommendation |
| ----- | -------------- |
| Exposure | Keep `/api/docs` enabled in staging; restrict or disable in production if the API is not public-facing. |
| Caching | The spec is generated at request time; no build step required. |
| Versioning | Bump `info.version` in `nelmio_api_doc.yaml` when the public contract changes. |
| Contract tests | Future slices can consume `/api/docs.json` for consumer-driven contract tests or SDK generation. |
| Security | Documented routes match the public API only; internal worker callbacks stay undocumented by design. |

---

# Architectural decisions

1. **NelmioApiDocBundle** — Symfony-native, attribute-driven, no runtime change to business logic.
2. **OpenAPI attributes on controllers** — colocated with routes; avoids duplicating paths in YAML.
3. **`disable_default_routes: true`** — only actions with OpenAPI attributes are documented; internal routes stay excluded without maintaining deny lists.
4. **OpenAPI 3.1** — aligned with current JSON Schema draft used by modern tooling.
5. **Presentation-only schemas** — `OpenApi/` subtree keeps documentation types out of Domain.

---

# Related documentation

- [Architecture index](./README.md)
- [CI pipeline](./ci.md)
- [Architecture rules](./architecture-rules.md)
