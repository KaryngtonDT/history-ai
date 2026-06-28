# Sprint 21 — Final Verification Report

Version: 1.0

Status: Accepted

Date: 2026-06-28

---

# Executive summary

Sprint 21 introduced a **Vector Store abstraction** and refactored semantic retrieval to route through it. Slice 4 changed **documentation and architecture verification only** — no business logic in backend, frontend, or worker.

| Area | Result |
| ---- | ------ |
| Backend PHPUnit | ✅ 517 tests, 1713 assertions |
| Backend architecture | ✅ 30 tests, 34 assertions |
| Backend OpenAPI | ✅ 27 tests, 293 assertions |
| Frontend build | ✅ OK |
| Frontend Vitest | ✅ 348 tests (74 files) |
| Frontend Biome | ✅ clean (392 files) |
| Worker pytest | ✅ 127 tests |
| Worker Ruff | ✅ All checks passed |
| Semantic Search API | ✅ Unchanged contract |

---

# Sprint 21 scope (slices 01–04)

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| S21-SLICE-01 | `VectorDocument`, `VectorDocumentCollection`, `VectorSearchResult`, `VectorSearchResultCollection`, `VectorStoreInterface` | ✅ |
| S21-SLICE-02 | `InMemoryVectorStore` adapter (cosine similarity, top-K, replace-on-index) | ✅ |
| S21-SLICE-03 | `SemanticRetriever` refactored to use `VectorStoreInterface`; handler indexes before search | ✅ |
| S21-SLICE-04 | Architecture docs, infrastructure guard test, full verification + this report | ✅ |

---

# VectorStoreInterface domain contract

```text
Chunk + EmbeddingVector
        │
        ▼
VectorDocument
        │
        ▼
VectorDocumentCollection
        │
        ▼
VectorStoreInterface
        ├── index(VectorDocumentCollection): void
        └── search(EmbeddingVector, int $limit = 5): VectorSearchResultCollection
        │
        ▼
VectorSearchResult (VectorDocument + SimilarityScore)
```

| Type | Role |
| ---- | ---- |
| `VectorDocument` | Immutable pair of `Chunk` + `EmbeddingVector` |
| `VectorDocumentCollection` | Ordered, immutable; `empty()` factory |
| `VectorSearchResult` | Search hit with `SimilarityScore` (0.0–1.0) |
| `VectorSearchResultCollection` | Ordered results; `empty()` factory |
| `VectorStoreInterface` | Pure domain port — no storage implementation in domain |

Domain lives in `backend/src/Domain/Semantic/` with no Symfony, HTTP, persistence, or AI provider dependencies.

---

# InMemoryVectorStore adapter

```text
Infrastructure/Semantic/InMemoryVectorStore
        │
        implements VectorStoreInterface
        │
        ├── index() replaces in-memory document set
        ├── search() cosine similarity (0–1 normalized)
        ├── top-K descending by score
        └── stable tie-break by insertion index
```

- No database, network, or AI provider.
- Wired via `services.yaml`: `VectorStoreInterface` → `InMemoryVectorStore`.
- Suitable for development, CI, and per-request semantic search until a persistent vector backend is introduced.

---

# SemanticRetriever refactor

**Before (Sprint 20):**

```text
SemanticRetriever
        │
        ├── embed query (EmbeddingGeneratorInterface)
        ├── cosine similarity over EmbeddedChunkCollection
        └── sort + top-K in retriever
```

**After (Sprint 21):**

```text
SearchSemanticChunksHandler
        │
        ├── chunk artifacts
        ├── generate embeddings
        ├── convert to VectorDocumentCollection
        └── VectorStoreInterface.index()

SemanticRetriever
        │
        ├── embed query (EmbeddingGeneratorInterface)
        ├── VectorStoreInterface.search()
        └── map VectorSearchResultCollection → RetrievedChunkCollection
```

| Decision | Rationale |
| -------- | --------- |
| Indexing in handler | Handler owns corpus preparation from artifacts |
| Search in retriever | Retriever owns query embedding and result mapping |
| Cosine in `InMemoryVectorStore` | Scoring/sorting delegated to vector store implementation |
| Shared store instance | Handler and retriever use same `VectorStoreInterface` per request |

---

# Semantic Search API compatibility

```text
GET /api/contents/{contentId}/semantic-search?q=…
        │
        ▼
SearchSemanticChunksHandler (unchanged HTTP contract)
        │
        ▼
{
  "results": [
    { "artifactId", "chunkId", "position", "text", "score" }
  ]
}
```

- Response shape unchanged from Sprint 20.
- OpenAPI schemas (`RetrievedChunk`, `SemanticSearchResult`) unchanged.
- Functional tests for semantic-search controller remain green.
- Frontend `SemanticSearchService` and `SemanticSearchPanel` unchanged.

---

# Architecture rules

## Backend

| Rule | Scope | Enforced by |
| ---- | ----- | ----------- |
| Semantic domain purity | `Domain/Semantic` | `LayerDependencyTest::testSemanticDomainDoesNotDependOnOuterLayersOrFrameworks` |
| Semantic vector domain | `VectorStoreInterface` + value objects | `LayerDependencyTest::testSemanticVectorDomainDoesNotDependOnOuterLayersOrFrameworks` |
| Semantic infrastructure | `Infrastructure/Semantic` | `LayerDependencyTest::testSemanticInfrastructureMayDependOnSemanticDomainOnly` |
| Semantic application isolation | `Application/Semantic` | `LayerDependencyTest::testSemanticApplicationMayDependOnSemanticArtifactAndContentDomainOnly` |
| Semantic presentation boundary | Controller + Response | `LayerDependencyTest::testSemanticPresentationMayDependOnSemanticApplicationOnly` |

## Frontend (unchanged from Sprint 20)

| Rule | Enforced by |
| ---- | ----------- |
| No `HttpSemanticSearchRepository` in features | `findFeatureSemanticTransportViolations` |
| `SemanticSearchPanel` uses `SemanticSearchService` only | Transport guard + panel unit tests |
| `SemanticSearchResults` props-only | Component unit tests + source guards |
| `fetch()` centralized in `HttpClient` | `findFetchViolations` |

Documented in `docs/architecture/architecture-rules.md`.

---

# Documentation verification

| Document | Location | Status |
| -------- | -------- | ------ |
| Architecture rules | `docs/architecture/architecture-rules.md` | ✅ vector store backend section |
| Architecture README | `docs/architecture/README.md` | ✅ Sprint 21 section |
| Sprint 21 report | `docs/reports/Sprint21-Verification.md` | ✅ This document |

---

# Validation summary

## Backend

```bash
docker compose run --rm --no-deps --entrypoint php -v "$(pwd)/backend:/var/www/html" backend bin/phpunit
```

```
OK (517 tests, 1713 assertions)
```

```bash
docker compose run --rm --no-deps --entrypoint php -v "$(pwd)/backend:/var/www/html" backend bin/phpunit tests/Architecture
```

```
OK (30 tests, 34 assertions)
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

1. **In-memory vector store only** — no persistence; corpus rebuilt and re-indexed on each semantic search request.
2. **Singleton store per container** — concurrent requests share the same `InMemoryVectorStore` instance; `index()` replaces corpus (acceptable for dev, not production scale).
3. **Deterministic embeddings** — hash-based vectors (dim 8); not production semantic quality.
4. **No worker indexing** — worker does not pre-chunk or pre-embed; indexing happens at query time in the handler.
5. **No pgvector / Qdrant / Pinecone** — `VectorStoreInterface` is ready but only `InMemoryVectorStore` exists.
6. **Backend image rebuild** — live Swagger requires image rebuild without bind mount after OpenAPI changes (unchanged from Sprint 20).

---

# Recommendations for Sprint 22

Sprint 21 completed the **Vector Store layer**. The natural next step is **explicit indexing and worker integration**:

| Priority | Feature | Rationale |
| -------- | ------- | --------- |
| 🥇 | **IndexSemanticContentHandler** | Explicit indexing use case separate from search |
| 🥈 | **Worker vector document generation** | Pre-chunk and pre-embed after artifact processing |
| 🥉 | **Persistent vector store** | `PgVectorStore` or external backend behind `VectorStoreInterface` |

**Suggested architecture:**

```text
Worker processing
        │
        ▼
Chunker → EmbeddingGeneratorInterface → VectorDocumentCollection
        │
        ▼
VectorStoreInterface.index() (persistent)

SearchSemanticChunksHandler
        │
        └── SemanticRetriever → VectorStoreInterface.search() (no re-index)
```

Alternatively, Sprint 22 could focus on **real embedding providers** (`GeminiEmbeddingGenerator`) while keeping `InMemoryVectorStore` — both extensions plug into existing ports without domain changes.

---

# CTO criteria (Sprint 21 closure)

| Criterion | Status |
| --------- | ------ |
| No business logic modified in final slice | ✅ |
| All test suites green | ✅ |
| Architecture tests green | ✅ |
| OpenAPI tests green | ✅ |
| Documentation up to date | ✅ |
| Verification report generated | ✅ |
| Semantic Search API contract preserved | ✅ |
| Frontend semantic UI unchanged | ✅ |

**Sprint 21 is officially closed.**

---

# Related commits (main branch)

| Commit | Message |
| ------ | ------- |
| `2d91425` | feat(semantic): add vector store domain contract |
| `65d07c0` | feat(semantic): add in-memory vector store adapter |
| `8cbdef5` | feat(semantic): refactor semantic retriever to use vector store |
| *(this slice)* | docs(semantic): add sprint 21 verification report |

---

# Test summary (delta from Sprint 20)

| Suite | Sprint 20 | Sprint 21 | Delta |
| ----- | --------- | --------- | ----- |
| Backend PHPUnit | 498 | 517 | +19 |
| Backend architecture | 28 | 30 | +2 |
| Backend OpenAPI | 27 | 27 | — |
| Frontend Vitest | 348 | 348 | — |
| Worker pytest | 127 | 127 | — |

---

# Sign-off

Verified by: automated suite execution + architecture layer tests

Environment: Docker Compose (backend PHP 8.4, frontend Node 22, worker Python 3.13)

Next sprint: **Sprint 22** — explicit indexing, worker integration, or persistent vector store
