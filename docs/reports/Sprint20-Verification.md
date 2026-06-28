# Sprint 20 — Final Verification Report

Version: 1.0

Status: Accepted

Date: 2026-06-28

---

# Executive summary

Sprint 20 delivered **semantic chunk retrieval end-to-end**: chunking domain, embedding abstraction, deterministic embeddings, in-memory retriever, semantic search API, frontend service, UI panel, OpenAPI documentation, and architecture verification. Slice 8 changed **documentation and OpenAPI only** — no business logic in backend, frontend, or worker.

| Area | Result |
| ---- | ------ |
| Backend PHPUnit | ✅ 498 tests, 1661 assertions |
| Backend architecture | ✅ 28 tests, 30 assertions |
| Backend OpenAPI | ✅ 27 tests, 293 assertions |
| Frontend build | ✅ OK |
| Frontend Vitest | ✅ 348 tests (74 files) |
| Frontend Biome | ✅ clean (392 files) |
| Worker pytest | ✅ 127 tests |
| Worker Ruff | ✅ All checks passed |
| OpenAPI / Swagger | ✅ `GET /api/contents/{contentId}/semantic-search` documented |

---

# Sprint 20 scope (slices 01–08)

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| S20-SLICE-01 | `Chunker`, `Chunk`, `ChunkCollection`, chunk value objects | ✅ |
| S20-SLICE-02 | `EmbeddingVector`, `EmbeddedChunk`, `EmbeddingGeneratorInterface` | ✅ |
| S20-SLICE-03 | `DeterministicEmbeddingGenerator` (hash-based, dim 8) | ✅ |
| S20-SLICE-04 | `SemanticRetriever`, `SemanticQuery`, `SimilarityScore`, `RetrievedChunk` | ✅ |
| S20-SLICE-05 | `SearchSemanticChunksHandler`, `GET /api/contents/{contentId}/semantic-search` | ✅ |
| S20-SLICE-06 | `SemanticSearchService`, Http/Mock repositories | ✅ |
| S20-SLICE-07 | `SemanticSearchPanel`, `SemanticSearchResults` on Processing page | ✅ |
| S20-SLICE-08 | OpenAPI schemas, architecture docs, full verification + this report | ✅ |

---

# Semantic chunking domain

```text
Artifact content (markdown)
        │
        ▼
Chunker.chunk(content)
        │
        ▼
ChunkCollection (ordered, immutable)
```

| Type | Role |
| ---- | ---- |
| `Chunk` | Immutable chunk with `ChunkId`, `ChunkPosition`, `ChunkText` |
| `ChunkCollection` | Ordered, immutable collection; empty allowed |
| `Chunker` | Splits artifact markdown into semantic chunks (section-aware) |

Domain lives in `backend/src/Domain/Semantic/` with no Symfony, HTTP, or persistence dependencies.

---

# Embedding abstraction

```text
ChunkCollection
        │
        ▼
EmbeddingGeneratorInterface.embed()
        │
        ▼
EmbeddedChunkCollection
```

| Type | Role |
| ---- | ---- |
| `EmbeddingVector` | Normalized float vector value object |
| `EmbeddedChunk` | Pairs `Chunk` + `EmbeddingVector` |
| `EmbeddingGeneratorInterface` | Pure domain port; no AI vendor coupling |

---

# DeterministicEmbeddingGenerator

```text
Infrastructure/Semantic/DeterministicEmbeddingGenerator
        │
        implements EmbeddingGeneratorInterface
        │
        ▼
Hash-based vectors (dimension 8, deterministic)
```

- No network, no external AI provider.
- Same input always produces the same vector (enables reproducible tests).
- Suitable for development and CI; not production semantic quality.

---

# SemanticRetriever

```text
EmbeddedChunkCollection + SemanticQuery
        │
        ▼
SemanticRetriever.retrieve()
        │
        ├── Cosine similarity per chunk
        ├── SimilarityScore (0.0–1.0)
        └── Sort descending by score (stable tie-break)
        │
        ▼
RetrievedChunkCollection
```

- In-memory only; no vector store persistence.
- Retriever operates on `EmbeddedChunkCollection` passed at query time (handler builds corpus from artifacts).

---

# Semantic Search API

```text
GET /api/contents/{contentId}/semantic-search?q=…
        │
        ▼
SearchSemanticChunksHandler
        │
        ├── Load artifacts for contentId
        ├── Chunker → embed → SemanticRetriever
        └── Map to RetrievedChunkResult DTOs
        │
        ▼
{
  "results": [
    { "artifactId", "chunkId", "position", "text", "score" }
  ]
}
```

- Read-only projection; no side effects.
- Invalid UUID, missing `q`, empty `q`, or query > 500 chars → HTTP 400.
- Unknown content → empty `results[]`.
- `score` is float 0.0–1.0 (cosine similarity).

---

# SemanticSearchService (frontend)

```text
GET /api/contents/{contentId}/semantic-search
        │
        ▼
HttpSemanticSearchRepository
        │
        ▼
SemanticSearchService.searchSemanticChunks()
        │
        ▼
SemanticSearchPanel (feature layer)
```

| Decision | Rationale |
| -------- | --------- |
| Repository factory (Http/Mock) | Consistent with Search, Graph, Recommendation |
| Invalid UUID / empty query → `[]` | Graceful degradation at service boundary |
| No frontend sort or score calculation | Backend owns ordering and similarity |
| Panel uses `SemanticSearchService` only | Preserves feature/service boundary |

---

# SemanticSearchPanel (frontend UI)

```text
ProcessingArtifacts
        │
        ├── artifact cards
        ├── ArtifactRelationsPanel
        ├── KnowledgeGraphPanel
        └── SemanticSearchPanel
                │
                ├── local query state (min 2 chars)
                ├── Search button + Enter key
                └── SemanticSearchResults (props-only)
```

| Component | Responsibility |
| --------- | -------------- |
| `SemanticSearchPanel` | Query input, loading/empty/error states, calls `SemanticSearchService` |
| `SemanticSearchResults` | Renders `RetrievedChunk[]` with score badge (`0.91`), artifact type, anchor links |
| `formatSemanticScore` | `score.toFixed(2)` — not percentage |

`ProcessingArtifacts` still calls `artifactService.listByContentId()` exactly once.

---

# OpenAPI / Swagger status

Verified via `tests/Functional/OpenApi/ApiDocumentationTest.php`:

| Check | Status |
| ----- | ------ |
| `/api/docs` (Swagger UI) available | ✅ |
| `/api/docs.json` generates OpenAPI 3.1 | ✅ |
| `GET /api/contents/{contentId}/semantic-search` documented | ✅ |
| Query parameter `q` (required, 1–500 chars) | ✅ |
| Response 200 → `#/components/schemas/SemanticSearchResult` | ✅ |
| Response 400 → `#/components/schemas/ErrorResponse` | ✅ |
| Schema `RetrievedChunk` with `artifactId`, `chunkId`, `position`, `text`, `score` | ✅ |
| `score` type `number`, minimum 0, maximum 1, example 0.87 | ✅ |
| Schema `SemanticSearchResult` with `results[]` | ✅ |

Browse locally: `http://localhost:8000/api/docs`

> **Note:** The backend image has no bind mount. After OpenAPI annotation changes, run `docker compose build backend && docker compose up -d backend --force-recreate` so the running container serves updated schemas. Workspace validation used bind mount: `docker compose run --rm --no-deps --entrypoint php -v "$(pwd)/backend:/var/www/html" backend bin/phpunit`.

---

# Architecture rules

## Backend

| Rule | Scope | Enforced by |
| ---- | ----- | ----------- |
| Semantic domain purity | `Domain/Semantic` | `LayerDependencyTest::testSemanticDomainDoesNotDependOnOuterLayersOrFrameworks` |
| Semantic application isolation | `Application/Semantic` | `LayerDependencyTest::testSemanticApplicationMayDependOnSemanticArtifactAndContentDomainOnly` |
| Semantic presentation boundary | `Presentation/Http/Controller/Semantic`, `Presentation/Http/Response/Semantic` | `LayerDependencyTest::testSemanticPresentationMayDependOnSemanticApplicationOnly` |

## Frontend

| Rule | Enforced by |
| ---- | ----------- |
| No `HttpSemanticSearchRepository` in features | `findFeatureSemanticTransportViolations` |
| No `SemanticSearchRepositoryFactory` in features | `findFeatureSemanticTransportViolations` |
| `SemanticSearchPanel` uses `SemanticSearchService` only | Transport guard + panel unit tests |
| `SemanticSearchResults` props-only (no service imports) | Component unit tests + source guards |
| `fetch()` centralized in `HttpClient` | `findFetchViolations` |

Documented in `docs/architecture/architecture-rules.md`.

---

# Documentation verification

| Document | Location | Status |
| -------- | -------- | ------ |
| OpenAPI guide | `docs/architecture/openapi.md` | ✅ semantic search endpoint + schemas |
| Architecture rules | `docs/architecture/architecture-rules.md` | ✅ semantic service layer section |
| Architecture README | `docs/architecture/README.md` | ✅ Sprint 20 section |
| Sprint 20 report | `docs/reports/Sprint20-Verification.md` | ✅ This document |

---

# Validation summary

## Backend

```bash
docker compose run --rm --no-deps --entrypoint php -v "$(pwd)/backend:/var/www/html" backend bin/phpunit
```

```
OK (498 tests, 1661 assertions)
```

```bash
docker compose run --rm --no-deps --entrypoint php -v "$(pwd)/backend:/var/www/html" backend bin/phpunit tests/Architecture
```

```
OK (28 tests, 30 assertions)
```

```bash
docker compose run --rm --no-deps --entrypoint php -v "$(pwd)/backend:/var/www/html" backend bin/phpunit tests/Functional/OpenApi/
```

```
OK (27 tests, 293 assertions)
```

## Frontend

```bash
docker compose exec frontend npm run build
docker compose exec frontend npm test
docker compose exec frontend npm run check
```

```
npm run build  ✓ built
npm test       348 passed (74 files)
npm run check  Checked 392 files. No fixes applied.
```

## Worker

```bash
docker compose exec worker pytest
docker compose exec worker ruff check .
```

```
127 passed, 1 warning in ~4s
All checks passed!
```

---

# Known limitations

1. **Deterministic embeddings only** — hash-based vectors (dim 8); not semantically meaningful for production retrieval quality.
2. **In-memory retrieval** — no vector store; corpus rebuilt from artifacts on each query.
3. **No worker indexing** — worker does not pre-chunk or pre-embed artifacts; chunking happens at query time in the handler.
4. **No persistence of embeddings** — embeddings are ephemeral per request.
5. **Cosine on small vectors** — similarity scores are deterministic but may not reflect real semantic relevance.
6. **Frontend min query length 2** — UI requires 2 characters; API accepts 1 (intentional UX guard).
7. **Backend image rebuild required** — code changes need image rebuild without bind mount for live Swagger.
8. **Manual search only** — no debounced live search; user triggers via button or Enter.
9. **Artifact type from parent props** — API chunks lack artifact type; UI maps `artifactId` → type from loaded artifacts.

---

# Recommendations for Sprint 21

Sprint 20 established the full **Retrieval** chain. Sprint 21 should introduce a **Vector Store** abstraction without breaking existing domain interfaces:

| Priority | Feature | Rationale |
| -------- | ------- | --------- |
| 🥇 | **VectorStoreInterface** | Decouple retriever from in-memory `EmbeddedChunkCollection` |
| 🥈 | **InMemoryVectorStore** | Testable vector index with cosine search |
| 🥉 | **Worker indexing pipeline** | Pre-chunk and pre-embed artifacts after processing |

**Suggested architecture:**

```text
Chunker
      │
      ▼
EmbeddingGeneratorInterface
      │
      ├── DeterministicEmbeddingGenerator (dev/CI)
      └── GeminiEmbeddingGenerator (future)
      │
      ▼
VectorStoreInterface
      │
      ├── InMemoryVectorStore
      └── PgVectorStore (future)
      │
      ▼
SemanticRetriever → Semantic Search API → SemanticSearchService → UI
```

This evolution preserves all abstractions built in Sprints 12–20 and enables swapping embedding providers and vector backends without domain changes.

---

# CTO criteria (Sprint 20 closure)

| Criterion | Status |
| --------- | ------ |
| No business logic modified in final slice | ✅ |
| All test suites green | ✅ |
| Architecture tests green | ✅ |
| OpenAPI tests green | ✅ |
| Documentation up to date | ✅ |
| Verification report generated | ✅ |

**Sprint 20 is officially closed.**

---

# Related commits (main branch)

| Commit | Message |
| ------ | ------- |
| `de99fc0` | feat(semantic): add semantic chunking domain |
| `49c4f8e` | feat(semantic): add embedding abstraction domain |
| `502fc75` | feat(semantic): add deterministic embedding generator adapter |
| `18409b4` | feat(semantic): add in-memory semantic retriever |
| `0a8c256` | feat(semantic): expose semantic search api |
| `906bf76` | feat(frontend): add semantic search service |
| `bd5a6c6` | feat(frontend): add semantic search UI panel |
| *(this slice)* | docs(semantic): document semantic search api and sprint 20 verification |

---

# Test summary (delta from Sprint 19)

| Suite | Sprint 19 | Sprint 20 | Delta |
| ----- | --------- | --------- | ----- |
| Backend PHPUnit | 423 | 498 | +75 |
| Backend architecture | 25 | 28 | +3 |
| Backend OpenAPI | 23 | 27 | +4 |
| Frontend Vitest | 316 | 348 | +32 |
| Worker pytest | 127 | 127 | — |

---

# Sign-off

Verified by: automated suite execution + OpenAPI schema tests

Environment: Docker Compose (backend PHP 8.4, frontend Node 22, worker Python 3.13)

Next sprint: **Sprint 21** — Vector Store & production retrieval (RAG foundation)
