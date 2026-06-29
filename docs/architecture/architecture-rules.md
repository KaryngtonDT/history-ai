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
15. **Graph domain** — `Domain/Graph` follows the same purity rules as other domains (no Symfony, Doctrine, Infrastructure, or Presentation).
16. **Graph application** — `Application/Graph` depends on `Domain/Graph`, `Domain/Relation`, `Domain/Artifact`, and `Domain/Content` only; must not import Infrastructure or Presentation.
17. **Graph presentation** — controllers, responses, and OpenAPI schemas under `Presentation/Http/.../Graph` and `Presentation/OpenApi/Schema/Graph*` / `KnowledgeGraph.php` may depend on `Application/Graph` and Domain value objects; must not import Infrastructure.
18. **Recommendation domain** — `Domain/Recommendation` follows the same purity rules as other domains (no Symfony, Doctrine, Infrastructure, or Presentation).
19. **Recommendation application** — `Application/Recommendation` depends on `Domain/Recommendation`, `Domain/Graph`, `Domain/Relation`, `Domain/Artifact`, and `Domain/Content` only; must not import Infrastructure or Presentation.
20. **Recommendation presentation** — controllers, responses, and OpenAPI schemas under `Presentation/Http/.../Recommendation` and `Presentation/OpenApi/Schema/RecommendedArtifact.php`, `ArtifactRecommendations.php`, `RecommendationReasonSchema.php` may depend on `Application/Recommendation` and Domain value objects; must not import Infrastructure.

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
| Feature modules must not import Graph transport (`HttpGraphRepository`, `GraphRepositoryFactory`, `GraphRepository`) | Graph UI uses `GraphService` only |
| Feature modules must not import Recommendation transport (`HttpRecommendationRepository`, `RecommendationRepositoryFactory`, `RecommendationRepository`) | Recommendations UI uses `RecommendationService` only |
| `InteractiveTimeline` must not import services or repositories | Structured timeline rendering is props-only |
| `InteractiveMap` must not import services or repositories | Map rendering is props-only |
| `InteractiveGraph` must not import services or repositories | Graph rendering is props-only |
| Timeline artifact renderers may import `TimelineService` | Service layer owns HTTP/mock wiring |
| Map panels may import `MapService` | Service layer owns HTTP/mock wiring |
| Relation panels may import `RelationService` | Service layer owns HTTP/mock wiring |
| Graph panels may import `GraphService` | Service layer owns HTTP/mock wiring |
| Recommendation panels may import `RecommendationService` | Service layer owns HTTP/mock wiring |

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
        └── KnowledgeGraphPanel (contentId)
```

### Graph service layer

```text
features/graph/KnowledgeGraphPanel
      │
      ▼
GraphService.getKnowledgeGraph()
      │
      ▼
GraphRepositoryFactory → HttpGraphRepository | MockGraphRepository
      │
      ▼
HttpClient (HTTP mode only)
```

Processing page graph integration:

```text
ProcessingArtifacts
        │
        ├── artifact cards (id="artifact-{type}" anchors)
        ├── ArtifactRelationsPanel
        └── KnowledgeGraphPanel → InteractiveGraph (props-only)
```

### Recommendation service layer

```text
features/recommendation/SeeAlsoRecommendationsPanel
      │
      ▼
RecommendationService.getArtifactRecommendations(contentId, artifactId)
      │
      ▼
RecommendationRepositoryFactory → HttpRecommendationRepository | MockRecommendationRepository
      │
      ▼
HttpClient (HTTP mode only)
```

Processing page recommendation integration:

```text
ProcessingArtifacts
        │
        ├── artifact cards (id="artifact-{type}" anchors)
        ├── SeeAlsoRecommendationsPanel (per existing artifact)
        ├── ArtifactRelationsPanel
        └── KnowledgeGraphPanel
```

### Semantic search service layer

```text
features/semantic/SemanticSearchPanel
      │
      ▼
SemanticSearchService.searchSemanticChunks(contentId, query)
      │
      ▼
SemanticSearchRepositoryFactory → HttpSemanticSearchRepository | MockSemanticSearchRepository
      │
      ▼
HttpClient (HTTP mode only)
```

Processing page semantic search integration:

```text
ProcessingArtifacts
        │
        ├── artifact cards (id="artifact-{type}" anchors)
        ├── ArtifactRelationsPanel
        ├── KnowledgeGraphPanel
        └── SemanticSearchPanel → SemanticSearchResults (props-only)
```

`SemanticSearchResults` is props-only and must not import services or repositories.

### Vector store layer (backend)

```text
SearchSemanticChunksHandler
        │
        ├── Chunker → ChunkCollection
        ├── EmbeddingGeneratorInterface → EmbeddedChunkCollection
        ├── VectorDocumentCollection
        ├── VectorStoreInterface.index()
        └── SemanticRetriever.retrieve()
                ├── EmbeddingGeneratorInterface (query embedding)
                └── VectorStoreInterface.search()
        │
        ▼
RetrievedChunkCollection → Semantic Search API JSON
```

| Layer | Rule |
| ----- | ---- |
| `Domain/Semantic` | Pure domain: `EmbeddingProviderInterface`, `VectorStoreInterface`, `VectorDocument`, `VectorSearchResult`, `SemanticRetriever` — no Symfony, HTTP, or persistence |
| `Infrastructure/Semantic` | `DeterministicEmbeddingProvider`, `GeminiEmbeddingProvider`, `EmbeddingProviderFactory`, `InMemoryVectorStore`; transport abstractions (`GeminiEmbeddingTransportInterface`); cosine similarity and top-K sorting |
| `Application/Semantic` | Handler indexes vector documents before calling `SemanticRetriever`; no vector persistence |
| `Presentation/Http/Semantic` | Thin HTTP adapter only; unchanged semantic-search contract |
| `Presentation/Console/Semantic` | Operational CLI only (e.g. Gemini smoke test); may depend on Infrastructure; not used by CI or default runtime |

Wiring (`services.yaml`):

```text
EMBEDDING_PROVIDER=deterministic|gemini
        │
        ▼
EmbeddingProviderFactory.create()
        │
        ├── DeterministicEmbeddingProvider   (default)
        └── GeminiEmbeddingProvider          (requires GEMINI_API_KEY)

EmbeddingGeneratorInterface → DeterministicEmbeddingGenerator
VectorStoreInterface → InMemoryVectorStore
SemanticRetriever    → autowired with VectorStoreInterface
```

Provider selection:

| `EMBEDDING_PROVIDER` | Result |
| -------------------- | ------ |
| `deterministic` (default) | SHA-256 `DeterministicEmbeddingProvider` — used in local dev and CI |
| `gemini` | `GeminiEmbeddingProvider` via Gemini `embedContent` REST API; requires `GEMINI_API_KEY` |
| unknown value | `InvalidEmbeddingProviderConfigurationException` at container build / first resolution |

Gemini env vars: `GEMINI_API_KEY`, `GEMINI_EMBEDDING_MODEL` (default `text-embedding-004`). Factory validates API key when `gemini` is selected; no network call during selection. Tests use mocked transport; no live API calls in CI.

Per-request flow: handler calls `index()` (replaces in-memory corpus), then retriever calls `search()`. No worker or frontend involvement in vector indexing.

### Embedding provider layer (backend)

```text
Chunk
    │
    ▼
EmbeddingGeneratorInterface
    │
    ▼
DeterministicEmbeddingGenerator
    │
    ▼
EmbeddingProviderInterface
    │
    ├── DeterministicEmbeddingProvider (default)
    └── GeminiEmbeddingProvider (optional)
            │
            ▼
EmbeddingVector
```

| Component | Role |
| --------- | ---- |
| `EmbeddingProviderInterface` | Domain port: `generateEmbedding(ChunkText): EmbeddingVector` |
| `DeterministicEmbeddingProvider` | SHA-256 deterministic vectors (8-dim); default for local dev and CI |
| `GeminiEmbeddingProvider` | Gemini `embedContent` REST API; requires `GEMINI_API_KEY` |
| `EmbeddingProviderFactory` | Selects provider from `EMBEDDING_PROVIDER` env var |
| `GeminiEmbeddingTransportInterface` | HTTP transport abstraction; mocked in tests |

Manual verification (not CI):

```bash
php bin/console semantic:embedding:smoke-test "Roman Empire"
```

Uses `GeminiEmbeddingProvider` directly; does not change runtime provider wiring.

### Chat provider layer (backend)

```text
AskContentChatHandler
        │
        ▼
ChatRequest (prompt + sources + options)
        │
        ▼
CHAT_PROVIDER=mock|gemini
        │
        ▼
ChatProviderFactory.create()
        │
        ├── MockChatProvider              (default)
        └── GeminiChatProvider            (requires GEMINI_API_KEY)
                │
                ▼
        GeminiChatTransportInterface
                │
                └── CurlGeminiChatTransport
```

| Component | Role |
| --------- | ---- |
| `ChatProviderInterface` | Domain port: `answer(ChatRequest): ChatResponse` |
| `MockChatProvider` | Deterministic local answer; default for local dev and CI |
| `GeminiChatProvider` | Gemini `generateContent` REST API; requires `GEMINI_API_KEY` |
| `ChatProviderFactory` | Selects provider from `CHAT_PROVIDER` env var |
| `GeminiChatTransportInterface` | HTTP transport abstraction; mocked in tests |
| `CurlGeminiChatTransport` | cURL implementation; not used in CI tests |

Provider selection:

| `CHAT_PROVIDER` | Result |
| --------------- | ------ |
| `mock` (default) | `MockChatProvider` — used in local dev and CI |
| `gemini` | `GeminiChatProvider` via Gemini `generateContent` REST API; requires `GEMINI_API_KEY` |
| unknown value | `InvalidChatProviderConfigurationException` at container build / first resolution |

Gemini chat env vars: `GEMINI_API_KEY`, `GEMINI_CHAT_MODEL` (default `gemini-2.5-flash`). Factory validates API key when `gemini` is selected; no network call during selection. Tests use mocked transport; no live API calls in CI.

### Chat UI layer (frontend)

```text
ProcessingArtifacts
        │
        ▼
ChatPanel (only component using ConversationService)
        │
        ├── ChatMessageList
        ├── ChatMessage
        ├── ChatInput
        └── SourcesPanel
```

| Component | Rule |
| --------- | ---- |
| `ChatPanel` | Calls `conversationService.askQuestion()` only |
| `ChatInput`, `ChatMessage`, `ChatMessageList`, `SourcesPanel` | Props-only; no service imports |
| `DocumentSelector` | Props-only; no service imports |
| `features/chat` | Must not import `HttpChatRepository`, `ChatRepositoryFactory`, `ChatRepository`, `HttpConversationRepository`, `ConversationRepositoryFactory`, or `ConversationRepository` |

Enforced by `findFeatureChatTransportViolations()` in `frontend/src/architecture/architectureRules.ts`.

```tsx
// frontend/src/features/chat/ChatPanel/ChatPanel.tsx
import { HttpChatRepository } from "@/services/chat/HttpChatRepository"; // ❌ forbidden
```

**Fix:** import `conversationService` from `@/services/conversation/ConversationService` in `ChatPanel` only.

### Persistent conversations (Platform Sprint 24)

```text
ChatPanel
        │
        ▼
ConversationService.askQuestion()
        │
        ▼
POST /api/contents/{contentId}/conversations/{conversationId}/chat
        │
        ▼
AskConversationChatHandler
        │
        ├── ConversationRepository (load / create / save)
        └── AskContentChatHandler (RAG delegation)
        │
        ▼
ConversationChatResponse JSON
        │
        ▼
ChatPanel renders conversation.messages (server source of truth)
```

| Component | Rule |
| --------- | ---- |
| `ConversationRepositoryInterface` | Domain port: `save`, `findById`, `findByContentId` |
| `AskConversationChatHandler` | Appends user message, delegates RAG, appends assistant message, persists |
| `ConversationService` (frontend) | Only entry point for conversation HTTP from features |
| `ChatPanel` | Owns `conversationId` + `chatResult`; no local message append |
| `chatService.streamQuestion()` | Preserved for future conversation streaming; unused in `ChatPanel` |

### Multi-document conversations (Platform Sprint 25)

```text
ChatPanel
        │
        ▼
DocumentSelector (props-only)
        │
        ▼
ConversationService.updateDocuments()
        │
        ▼
PUT /api/conversations/{conversationId}/documents
        │
        ▼
Conversation.documents[] (persisted)
        │
        ▼
POST /api/contents/{contentId}/conversations/{conversationId}/chat
        │
        ▼
AskConversationChatHandler → RAG across all selected documents
```

| Component | Rule |
| --------- | ---- |
| `SelectedDocument`, `SelectedDocumentCollection` | Domain value objects; deduplication preserves order |
| `UpdateConversationDocumentsHandler` | Replaces document selection; preserves messages; no RAG |
| `DocumentSelector` | Props-only; emits `onSelectionChange(contentIds)`; no service imports |
| `ChatPanel` | Owns selection via `conversation.documents`; backend response is source of truth |
| `ConversationService.updateDocuments()` | Only frontend entry point for document selection HTTP |

### Interactive citations (UX-02)

```text
ChatResponse.citations[]
        │
        ▼
ChatAnswer JSON (citations[] without text)
        │
        ▼
Frontend ChatService / repositories
        │
        ▼
ChatMessage [1] buttons + SourcesPanel rows
        │
        ▼
ProcessingArtifacts.handleCitationClick
        │
        ├── scrollIntoView(#artifact-{type})
        └── .history-ai-highlight (3s fade)
```

| Component | Rule |
| --------- | ---- |
| `ChatCitation` (API) | `number`, `artifactId`, `chunkId`, `score` — no `text` |
| `ChatMessage` | Parses `/\[(\d+)\]/g`; emits `{ chunkId, artifactId }` on click |
| `citationNavigation.ts` | Scroll + highlight utilities; no fetch |
| `features/chat/ChatMessage` | Must not import `@/services/` (architecture test) |

Citations are **presentation navigation** only — domain and provider logic unchanged after slice 1.

### Streaming chat (UX-03)

```text
ChatPanel
        │
        ▼
ChatService.streamQuestion()
        │
        ▼
POST /api/contents/{contentId}/chat/stream
        │
        ▼
SSE token events (ChatStreamToken)
        │
        ▼
Assistant bubble grows progressively
```

| Component | Rule |
| --------- | ---- |
| `StreamingChatProviderInterface` | Separate from `ChatProviderInterface`; opt-in streaming |
| `POST /chat/stream` | `text/event-stream`; `token` + `done` events |
| `ChatStreamToken` (OpenAPI) | `index`, `text` — token payload only |
| `HttpChatRepository` | Only place allowed to use `fetch()` for SSE (exception to HttpClient rule) |
| `ChatPanel` | Uses `streamQuestion()` for legacy single-turn streaming; persistent chat uses `ConversationService` |

Streaming does not yet emit sources/citations — use non-streaming `/chat` for full metadata.

### Platform observability (Platform Sprint 23)

```text
HTTP request
        │
        ▼
RequestCorrelationIdListener
        │
        ├── X-Correlation-ID header (in/out)
        └── RequestContext (correlationId)
        │
        ▼
SearchSemanticChunksHandler / AskContentChatHandler / AskContentChatStreamHandler
        │
        ├── PerformanceTimer (per stage)
        └── CompositePerformanceMetricsRecorder
                ├── LoggingPerformanceMetricsRecorder → PlatformLogger
                └── InMemoryPerformanceMetricsStore (ring buffer, max 100)
        │
        ▼
GET /internal/platform/metrics?limit=20
        │
        ▼
PlatformMetricsResponse JSON (snapshots[], newest first)
```

| Component | Role |
| --------- | ---- |
| `CorrelationId` | Domain value object (UUID); propagated via `RequestContext` |
| `PerformanceTimer` | Application helper; records named stage durations in milliseconds |
| `InMemoryPerformanceMetricsStore` | Infrastructure ring buffer; exposes recent snapshots via reader port |
| `CachedEmbeddingProvider` | Wraps `EmbeddingProviderInterface`; LRU in-memory cache (max 1000 keys) |
| `GET /internal/platform/metrics` | Internal diagnostic endpoint; optional `limit` query (1–100, default 20) |

Embedding cache wiring:

```text
EmbeddingProviderFactory
        │
        ▼
CachedEmbeddingProvider
        │
        ├── InMemoryEmbeddingCache (LRU)
        └── UncachedEmbeddingProvider (deterministic | gemini)
```

`CachedEmbeddingProvider` must not import Application layer types (architecture test enforces Semantic infra boundary).

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

```tsx
// frontend/src/features/graph/KnowledgeGraphPanel/KnowledgeGraphPanel.tsx
import { HttpGraphRepository } from "@/services/graph/HttpGraphRepository"; // ❌ forbidden
```

**Fix:** import `graphService` from `@/services/graph/GraphService`.

```tsx
// frontend/src/features/graph/InteractiveGraph/InteractiveGraph.tsx
import { graphService } from "@/services/graph/GraphService"; // ❌ forbidden
```

**Fix:** receive graph data via props from `KnowledgeGraphPanel`.

```tsx
// frontend/src/features/recommendation/SeeAlsoRecommendationsPanel/SeeAlsoRecommendationsPanel.tsx
import { HttpRecommendationRepository } from "@/services/recommendation/HttpRecommendationRepository"; // ❌ forbidden
```

**Fix:** import `recommendationService` from `@/services/recommendation/RecommendationService`.

```tsx
// frontend/src/features/semantic/SemanticSearchPanel/SemanticSearchPanel.tsx
import { HttpSemanticSearchRepository } from "@/services/semantic/HttpSemanticSearchRepository"; // ❌ forbidden
```

**Fix:** import `semanticSearchService` from `@/services/semantic/SemanticSearchService`.

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
