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
| Feature modules must not import Agent transport (`HttpAgentRepository`, `AgentRepositoryFactory`, `AgentRepository`) | Agent UI uses `AgentService` only |
| Feature modules must not import Recommendation transport (`HttpRecommendationRepository`, `RecommendationRepositoryFactory`, `RecommendationRepository`) | Recommendations UI uses `RecommendationService` only |
| `InteractiveTimeline` must not import services or repositories | Structured timeline rendering is props-only |
| `InteractiveMap` must not import services or repositories | Map rendering is props-only |
| `InteractiveGraph` must not import services or repositories | Graph rendering is props-only |
| Timeline artifact renderers may import `TimelineService` | Service layer owns HTTP/mock wiring |
| Map panels may import `MapService` | Service layer owns HTTP/mock wiring |
| Relation panels may import `RelationService` | Service layer owns HTTP/mock wiring |
| Graph panels may import `GraphService` | Service layer owns HTTP/mock wiring |
| Agent panels may import `AgentService` | Service layer owns HTTP/mock wiring |
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
      ├── GraphService.getKnowledgeGraph(contentId)           — full content graph
      ├── GraphService.getGraphNeighborhood(contentId, id)   — one-hop neighborhood
      └── GraphService.getConversationGraph(conversationId)    — conversation-scoped graph
      │
      ▼
GraphRepositoryFactory → HttpGraphRepository | MockGraphRepository
      │
      ▼
HttpClient (HTTP mode only)
```

**KnowledgeGraphPanel** loads the content graph by default. When `conversationId` is provided, it loads the conversation-scoped graph instead. Node clicks call `getGraphNeighborhood()` for highlight data. The panel must not import graph repositories directly.

**InteractiveGraph** is props-only: selected node, neighbor nodes, and highlighted edges are passed from the panel. It emits `onNodeSelect(artifactId)` on click.

Processing page graph integration:

```text
ProcessingArtifacts
        │
        ├── artifact cards (id="artifact-{type}" anchors)
        ├── ArtifactRelationsPanel
        └── KnowledgeGraphPanel → InteractiveGraph (props-only)
              │
              optional conversationId (future ChatPanel wiring)
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
        ├── AgentModePanel → AgentExecutionTrace (props-only)
        └── SemanticSearchPanel → SemanticSearchResults (props-only)
```

`SemanticSearchResults` is props-only and must not import services or repositories.

### Agent service layer

```text
features/agent/AgentModePanel
      │
      ▼
AgentService.runAgent(contentId, question, conversationId?)
      │
      ▼
AgentRepositoryFactory → HttpAgentRepository | MockAgentRepository
      │
      ▼
HttpClient (HTTP mode only)
      │
      ▼
POST /api/contents/{contentId}/agent/run
```

| Component | Rule |
| --------- | ---- |
| `AgentModePanel` | Owns question, loading, error, and result state; calls `agentService.runAgent()` only |
| `AgentExecutionTrace` | Props-only display of `plan[]`, `steps[]`, `finalSummary`; composes `AgentMetadataPanel` |
| `AgentService` | Validates UUIDs and question length; returns `EMPTY_AGENT_EXECUTION` on client-side invalid input or HTTP 400 |
| `HttpAgentRepository` | Only agent repository using `HttpClient.post()`; no direct `fetch()` |
| `DeterministicAgentPlanner` | Keyword-based plan only; no LLM planner |
| `AgentMetadataPanel` | Props-only; renders per-tool metadata sections from `execution.steps[].metadata` |

### Agent tool execution (backend)

```text
RunAgentHandler
      │
      ▼
AgentToolExecutorInterface
      │
      ▼
CompositeAgentToolExecutor
      ├── SemanticSearchToolExecutor → SearchSemanticChunksHandler
      ├── KnowledgeGraphToolExecutor → GetKnowledgeGraphHandler
      ├── ConversationMemoryAgentToolExecutor → ConversationMemoryToolExecutor → ConversationRepositoryInterface
      ├── MultiDocumentChatToolExecutor → AskConversationChatHandler
      └── NullAgentToolExecutor (unused fallback)
      │
      ▼
AgentMetadataCollection.merge() → AgentExecutionResult.metadata
```

| Component | Rule |
| --------- | ---- |
| `AgentToolExecutorInterface` | Domain port; `RunAgentHandler` depends on this only — not concrete tool classes |
| `CompositeAgentToolExecutor` | Routes by `AgentTool` enum; all four tools have real executors |
| `SemanticSearchToolExecutor` | Delegates to `SearchSemanticChunksHandler`; metadata: `resultCount`, `topScore` |
| `KnowledgeGraphToolExecutor` | Delegates to `GetKnowledgeGraphHandler`; metadata: `nodeCount`, `edgeCount` |
| `ConversationMemoryToolExecutor` | Loads conversation via `ConversationRepositoryInterface`; metadata: `messageCount`, `userMessages`, `assistantMessages` |
| `MultiDocumentChatToolExecutor` | Delegates to `AskConversationChatHandler` when `conversationId` present; metadata: `messageCount`, `sourceCount`, `citationCount` or `requiresConversation` |
| `AgentMetadata` / `AgentMetadataCollection` | Domain types; merge step metadata with later-wins policy |
| `AgentMetadataPanel` | Frontend feature; maps tool metadata keys to human-readable labels |
| Continue-on-failure | Failed tool step marks `failed` summary; remaining steps still execute |

Infrastructure executors must not call HTTP or duplicate Application handler logic.

Processing page agent integration:

```text
ProcessingArtifacts
        │
        ├── artifact cards (id="artifact-{type}" anchors)
        ├── ArtifactRelationsPanel
        ├── KnowledgeGraphPanel
        ├── AgentModePanel
        └── SemanticSearchPanel
```

`AgentModePanel` accepts optional `conversationId` for future ChatPanel wiring; omitted on the processing page in Sprint 28 slice 4.

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
| `ChatPanel` | Calls `conversationService.streamQuestion()` for questions; `updateDocuments()` for selection |
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
| `ChatPanel` | Owns `conversationId` + `chatResult`; streams via `streamQuestion()`; `conversation` SSE event is source of truth |
| `chatService.streamQuestion()` | Preserved for single-turn streaming; unused in `ChatPanel` |
| `ConversationService.askQuestion()` | Preserved for non-streaming callers; unused in `ChatPanel` |

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

### Conversation streaming (Platform Sprint 26)

```text
ChatPanel
        │
        ▼
ConversationService.streamQuestion()
        │
        ▼
POST /api/contents/{contentId}/conversations/{conversationId}/chat/stream
        │
        ▼
AskConversationChatStreamHandler
        │
        ├── ContentChatStreamer (multi-doc RAG + StreamingChatProviderInterface)
        └── ConversationRepository (persist after full stream)
        │
        ▼
SSE: token → conversation → done
        │
        ▼
ChatPanel replaces local messages with conversation event payload
```

| Component | Rule |
| --------- | ---- |
| `ConversationStream` | Domain aggregate for streamed tokens; `toAssistantMessage()` after completion |
| `AskConversationChatStreamHandler` | Mirrors non-streaming flow; persists once after stream completes |
| `ContentChatStreamer` | Shared RAG streaming pipeline; does not change `AskConversationChatHandler` |
| `HttpConversationRepository` | Allowed to use `fetch()` for SSE (with `HttpChatRepository`) |
| `ChatPanel` | Optimistic user message + growing assistant bubble during tokens; `conversation` event is source of truth |
| `DocumentSelector` | Unchanged; still uses `updateDocuments()` only |

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
ChatPanel (legacy single-turn — not used after Sprint 26)
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
| `POST /chat/stream` | `text/event-stream`; `token` + `done` events (single-turn) |
| `POST …/conversations/{id}/chat/stream` | `text/event-stream`; `token` + `conversation` + `done` (Platform Sprint 26) |
| `ChatStreamToken` (OpenAPI) | `index`, `text` — token payload for both stream endpoints |
| `ConversationStreamEvent` (OpenAPI) | `conversation` — persisted conversation payload in SSE |
| `HttpChatRepository` / `HttpConversationRepository` | Only places allowed to use `fetch()` for SSE |
| `ChatPanel` | Uses `ConversationService.streamQuestion()` for persistent multi-doc chat |

Streaming does not yet emit sources/citations — use non-streaming `POST …/chat` for full metadata.

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

### Video processing foundation (Platform Sprint 31)

```text
Frontend VideoUploadPanel (/video/upload)
        │
        ▼
VideoService.validateVideo() + uploadVideo()
        │
        ▼
HttpVideoRepository → HttpClient.postFormData()
        │
        ▼
POST /api/videos (multipart/form-data, field: video)
        │
        ▼
UploadVideoHandler
        ├── VideoExtension.fromFilename()
        ├── VideoUploadSize.assertWithinLimit()
        ├── LocalVideoStorage → var/video-storage/{videoId}.{ext}
        ├── VideoJob.withStoragePath().queue()
        ├── DoctrineVideoRepository.save()
        └── MessengerVideoProcessingQueue → ProcessVideoMessage (sync transport)
        │
        ▼
HTTP 201 { videoId, status: "queued" }
```

| Component | Role |
| --------- | ---- |
| `VideoJob` | Immutable aggregate: `Uploaded → Queued → Processing → Completed/Failed` |
| `VideoExtension` | Domain rule: mp4, mov, mkv only |
| `VideoUploadSize` | Domain rule: max bytes from `VIDEO_UPLOAD_MAX_BYTES` |
| `LocalVideoStorage` | Stores uploaded binary on disk before queue dispatch |
| `ProcessVideoMessageHandler` | Delegates to `ProcessVideoHandler` for STT transcription |
| `VideoUploadPanel` | Drag-and-drop, progress bar, success/error states |
| `HttpClient.postFormData()` | XHR-based multipart upload with progress callbacks |

Feature components must use `videoService`, not `HttpVideoRepository` or `HttpClient` directly.

### Speech-to-text foundation (Platform Sprint 32)

```text
ProcessVideoMessage (Messenger)
        │
        ▼
ProcessVideoHandler
        ├── VideoJob.startProcessing()
        ├── SpeechToTextProviderFactory → FasterWhisperProvider
        ├── TranscriptRepository.save()
        ├── ArtifactRepository.create(ArtifactType::Transcript)
        └── VideoJob.complete()
        │
        ▼
GET /api/videos/{videoId}/transcript
        │
        ▼
GetVideoTranscriptHandler → TranscriptRepository.findByVideoId()
        │
        ▼
Frontend TranscriptPanel (/video/:videoId/transcript)
        │
        └── TranscriptService → HttpTranscriptRepository
```

| Component | Role |
| --------- | ---- |
| `Transcript` | Immutable domain aggregate with segments, language, duration |
| `SpeechToTextProviderInterface` | Domain port: `transcribe(VideoJob): Transcript` |
| `FasterWhisperProvider` | Infrastructure adapter invoking faster-whisper CLI |
| `SpeechToTextProviderFactory` | Selects provider from `STT_PROVIDER` env |
| `ProcessVideoHandler` | Application orchestration: STT → persist → artifact |
| `TranscriptJsonMapper` | Application-layer JSON serialization for persistence |
| `TranscriptPanel` | Read-only viewer with timestamps and segment highlight |

Feature components must use `transcriptService`, not `HttpTranscriptRepository` or `HttpClient` directly.

### Multilingual translation foundation (Platform Sprint 33)

```text
Transcript Artifact
        │
        ▼
ProcessVideoHandler (TRANSLATION_LANGUAGES=fr,de)
        ├── TranslationProviderFactory → OllamaTranslationProvider (Qwen)
        ├── TranslationRepository.save() (one row per language)
        └── ArtifactRepository.create(ArtifactType::Translation) × N
        │
        ▼
GET /api/videos/{videoId}/translations
GET /api/videos/{videoId}/translations/{language}
POST /api/videos/{videoId}/translations
        │
        ▼
Frontend TranslationPanel (/video/:videoId/translations)
        │
        └── TranslationService → HttpTranslationRepository
```

| Component | Role |
| --------- | ---- |
| `Translation` | Immutable domain aggregate with source/target language and segments |
| `TranslationProviderInterface` | Domain port: `translate(Transcript, TranslationLanguage): Translation` |
| `OllamaTranslationProvider` | Infrastructure adapter invoking Ollama (Qwen 3) |
| `TranslationProviderFactory` | Selects provider from `TRANSLATION_PROVIDER` env |
| `VideoTranslationGenerator` | Application orchestration: STT transcript → translate → persist → artifact |
| `TranslationJsonMapper` | Application-layer JSON serialization for persistence |
| `TranslationPanel` | Read-only viewer with language tabs and side-by-side comparison |

Feature components must use `translationService`, not `HttpTranslationRepository` or `HttpClient` directly.

### AI Engine Platform (Platform Sprint 34)

```text
Application Handler
        │
        ▼
AIProviderResolverInterface (capability)
        │
        ▼
AIEngineRegistry → enabled provider
        │
        ▼
SpeechToTextProvider / TranslationProvider / TextToSpeechProvider / (future VoiceClone, LipSync)
        │
        ▼
GET /api/ai/providers → AIEngineSettings (/settings/ai)
```

| Component | Role |
| --------- | ---- |
| `AIEngine` | Immutable aggregate per capability with provider list |
| `AIEngineRegistry` | Central registry of engines and providers |
| `AIProviderResolverInterface` | Domain port: resolve provider by capability |
| `AIEngineRegistryFactory` | Registers FasterWhisper, Ollama, and future disabled providers |
| `AIEngineSettings` | Read-only frontend overview of available engines |

Feature components must use `aiEngineService`, not `HttpAIEngineRepository` or `HttpClient` directly.

### Text-to-Speech Foundation (Platform Sprint 35)

```text
Translation Artifact
        │
        ▼
AIProviderResolverInterface.resolveTextToSpeech()
        │
        ▼
F5TextToSpeechProvider
        │
        ▼
Audio Artifact (ArtifactType::Audio)
        │
        ▼
GET/POST /api/videos/{videoId}/audio → AudioPlayerPanel (/video/:videoId/audio)
```

| Component | Role |
| --------- | ---- |
| `AudioArtifact` | Immutable aggregate with voice, duration, format, storage path |
| `TextToSpeechProviderInterface` | Domain port: `synthesize(Translation, Voice)` |
| `F5TextToSpeechProvider` | F5-TTS process runner and audio mapper |
| `VideoAudioGenerator` | Orchestrates translation → TTS → persistence → artifact |
| `AudioPlayerPanel` | Voice selection, generate, play/pause, download |

Feature components must use `audioService`, not `HttpAudioRepository` or `HttpClient` directly.

### Voice Cloning Foundation (Platform Sprint 36)

```text
Generic Audio (F5-TTS)
        │
        ▼
AIProviderResolverInterface.resolveVoiceClone()
        │
        ▼
OpenVoiceProvider (SeedVC disabled)
        │
        ▼
VoiceCloneArtifact (ArtifactType::VoiceClone)
        │
        ▼
GET/POST /api/videos/{videoId}/voice-clone → VoiceClonePanel (/video/:videoId/voice-clone)
```

| Component | Role |
| --------- | ---- |
| `VoiceProfile` | Immutable reference voice metadata (language, duration, sample rate) |
| `VoiceCloneArtifact` | Immutable aggregate linking source and cloned audio |
| `VoiceCloneProviderInterface` | Domain port: `cloneVoice(AudioArtifact, Translation)` — separate from TTS |
| `OpenVoiceProvider` | OpenVoice V2 process runner and voice clone mapper |
| `VideoVoiceCloneGenerator` | Orchestrates generic audio → voice clone → persistence |
| `VoiceClonePanel` | Generic/clone toggle, compare mode, dual preview players |

Feature components must use `voiceCloneService`, not `HttpVoiceCloneRepository` or `HttpClient` directly.

## Platform Sprint 37 — Lip Sync Foundation

```text
Original Video + VoiceCloneArtifact
        │
        ▼
AIProviderResolverInterface.resolveLipSync()
        │
        ▼
LatentSyncProvider (Wav2Lip disabled)
        │
        ▼
LipSyncArtifact (ArtifactType::LipSync)
        │
        ▼
GET/POST /api/videos/{videoId}/lip-sync → LipSyncPanel (/video/:videoId/lip-sync)
```

| Component | Role |
| --------- | ---- |
| `LipSyncArtifact` | Immutable aggregate linking source video, cloned audio, synced video |
| `LipSyncProviderInterface` | Domain port: `synchronize(VideoJob, VoiceCloneArtifact)` |
| `LatentSyncProvider` | LatentSync process runner and lip sync mapper |
| `VideoLipSyncGenerator` | Orchestrates voice clone → lip sync → persistence |
| `LipSyncPreview` | Before/after video comparison, replay, provider badge |

Feature components must use `lipSyncService`, not `HttpLipSyncRepository` or `HttpClient` directly.

## Platform Sprint 38 — Final Video Rendering

```text
LipSyncArtifact
        │
        ▼
AIProviderResolverInterface.resolveVideoRender()
        │
        ▼
FFmpegVideoRenderProvider
        │
        ▼
FinalVideoArtifact (ArtifactType::FinalVideo)
        │
        ▼
GET/POST /api/videos/{videoId}/render → FinalVideoPanel (/video/:videoId/render)
```

| Component | Role |
| --------- | ---- |
| `FinalVideoArtifact` | Immutable aggregate: finalVideoId, lipSyncArtifactId, provider, format, quality |
| `VideoRenderProviderInterface` | Domain port: `render(LipSyncArtifact, format, quality)` |
| `FFmpegVideoRenderProvider` | FFmpeg process runner and render mapper |
| `VideoFinalRenderGenerator` | Orchestrates lip sync → FFmpeg render → persistence |
| `FinalVideoPlayer` | Video preview, provider/quality badge, MP4 download |

Feature components must use `videoRenderService`, not `HttpVideoRenderRepository` or `HttpClient` directly.

## Platform Sprint 39 — Pipeline Configuration

```text
PipelineBuilder (/settings/pipeline)
        │
        ▼
PipelineService → HttpPipelineRepository
        │
        ▼
GET/PUT /api/pipeline, POST /api/pipeline/reset
        │
        ▼
PipelineConfiguration (Doctrine)
        │
        ▼
AIProviderResolver.resolve*() → configured provider or registry default
```

| Component | Role |
| --------- | ---- |
| `PipelineConfiguration` | Immutable aggregate: one enabled provider per `PipelineStageType` |
| `PipelineConfigurationRepositoryInterface` | Port: save, findLatest, deleteAll |
| `SavePipelineConfigurationHandler` | Validates enabled providers and persists configuration |
| `AIProviderResolver` | Resolves runtime providers from saved pipeline config with fallback |
| `PipelineBuilder` | Stage dropdowns, save, reset defaults at `/settings/pipeline` |
| `PipelineStageSelector` | Single-stage provider dropdown (enabled providers only) |

Feature components must use `pipelineService`, not `HttpPipelineRepository` or `HttpClient` directly.

## Platform Sprint 40 — AI Orchestrator

```text
Video Upload (/video/upload)
        │
        ├── Manual → saved PipelineConfiguration
        │
        └── Automatic → DeterministicPipelinePlanner
                │
                ▼
        RuntimePipelineConfigurationContext (ephemeral)
                │
                ▼
        AIProviderResolver → processing pipeline
```

| Component | Role |
| --------- | ---- |
| `ProcessingMode` | Enum: manual or automatic processing |
| `ProcessingStrategy` | Enum: balanced, quality, speed, low_memory |
| `PipelineRecommendation` | Immutable aggregate with pipeline config and estimates |
| `DeterministicPipelinePlanner` | Selects enabled providers from AI registry based on video analysis |
| `RuntimePipelineConfigurationContext` | Holds ephemeral automatic configuration per job |
| `ProcessingModeSelector` | Manual/automatic toggle on video upload |
| `PipelineRecommendationPanel` | Strategy, duration, quality, VRAM preview |

Feature components must use `orchestratorService`, not `HttpOrchestratorRepository` or `HttpClient` directly.

## Platform Sprint 41 — AI Director

```text
Video
        │
        ▼
CompositeVideoAnalyzer (Audio + Visual + Speech)
        │
        ▼
VideoIntelligence
        │
        ▼
DeterministicPipelinePlanner → PipelineRecommendation.reasons[]
```

| Component | Role |
| --------- | ---- |
| `VideoIntelligence` | Immutable aggregate describing audio, visual, and speech characteristics |
| `CompositeVideoAnalyzer` | Deterministic multi-analyzer engine (no LLM) |
| `VideoJobVideoIntelligenceFactory` | Builds intelligence from video job and optional transcript |
| `DeterministicPipelinePlanner` | Uses intelligence signals (speakers, confidence, music, lighting, lip visibility) |
| `VideoIntelligenceDashboard` | Upload preview with intelligence metrics and recommendation reasons |
| `VideoIntelligenceService` | Repository-backed intelligence loading |

Feature components must use `videoIntelligenceService`, not `HttpVideoIntelligenceRepository` or `HttpClient` directly.

## Platform Sprint 42 — Execution Optimization

```text
VideoIntelligence
        │
        ▼
DeterministicExecutionOptimizer → ExecutionOptimization
        │
        ▼
RuntimeExecutionOptimizationContext → Pipeline providers
```

| Component | Role |
| --------- | ---- |
| `ExecutionOptimization` | Immutable aggregate of stage parameters and explanations |
| `DeterministicExecutionOptimizer` | Rule engine (beam size, chunk size, style, stability, strength, preset) |
| `RuntimeExecutionOptimizationContext` | Holds ephemeral optimization per job |
| `OptimizationDashboard` | Upload preview with stage parameters and estimated impact |
| `OptimizationService` | Repository-backed optimization loading |

Feature components must use `optimizationService`, not `HttpOptimizationRepository` or `HttpClient` directly.

## Platform Sprint 43 — Resource Scheduling

```text
ExecutionOptimization
        │
        ▼
DeterministicPipelineScheduler → ExecutionSchedule
        │
        ▼
RuntimeExecutionScheduleContext → ProcessVideoHandler progress
```

| Component | Role |
| --------- | ---- |
| `ExecutionSchedule` | Immutable aggregate of scheduled stages and queue metrics |
| `DeterministicPipelineScheduler` | Assigns CPU/GPU/IO queues with strategy-aware concurrency |
| `RuntimeExecutionScheduleContext` | Holds ephemeral schedule and progress per job |
| `ProcessingResourceMonitor` | Upload preview with queue badges and stage timeline |
| `SchedulerService` | Repository-backed schedule loading |

Feature components must use `schedulerService`, not `HttpSchedulerRepository` or `HttpClient` directly.

## Platform Sprint 44 — Quality Assessment

```text
Final Render
        │
        ▼
VideoQualityAssessmentRunner → QualityReport artifact
        │
        ▼
GET /api/videos/{videoId}/quality → QualityDashboard
```

| Component | Role |
| --------- | ---- |
| `QualityReport` | Immutable aggregate of category scores, overall score, and recommendation |
| `DeterministicQualityEvaluator` | Rule-based scoring from intelligence, optimization, and final video |
| `VideoQualityAssessmentRunner` | Post-render assessment with non-blocking artifact persistence |
| `QualityDashboard` | Upload preview with per-category scores and publication recommendation |
| `QualityService` | Repository-backed quality report loading |

Feature components must use `qualityService`, not `HttpQualityRepository` or `HttpClient` directly.

## Platform Sprint 45 — Project Workspace

```text
Workspace
        │
        ▼
Project → BatchJob → Scheduler → Existing Pipeline
        │
        ▼
Multiple Final Videos + aggregate batch progress
```

| Component | Role |
| --------- | ---- |
| `Project` | Immutable aggregate of project metadata and video collection |
| `BatchJob` | Immutable batch state with status and aggregate progress |
| `RunBatchProcessingHandler` | Enqueues one pipeline job per video with failure isolation |
| `BatchJobProgressUpdater` | Updates batch progress when worker completes each video |
| `WorkspacePage` | Project list, video grid, language selection, batch progress |
| `WorkspaceService` | Repository-backed project and batch operations |

Feature components must use `workspaceService`, not `HttpWorkspaceRepository` or `HttpClient` directly.

## Platform Sprint 46 — Execution History

```text
Render Finished → Execution Snapshot → Execution History
        │
        ▼
Compare Versions → Reprocess (clone config + optional overrides)
```

| Component | Role |
| --------- | ---- |
| `ExecutionHistory` | Append-only aggregate of immutable execution versions |
| `ExecutionSnapshot` | Captures pipeline, optimization, quality, and render references |
| `RecordExecutionHistoryHandler` | Persists a version after successful render |
| `CompareExecutionHandler` | Diff providers, optimization, and quality scores |
| `ReprocessExecutionHandler` | Clones version config and re-queues pipeline |
| `ExecutionHistoryPanel` | Timeline, compare, and replay actions in workspace UI |
| `HistoryService` | Repository-backed history, compare, and reprocess operations |

Feature components must use `historyService`, not `HttpHistoryRepository` or `HttpClient` directly.

## Platform Sprint 47 — AI Review & Feedback Loop

```text
Quality Assessment → User Review → Preference Profile → AI Director
```

| Component | Role |
| --------- | ---- |
| `Review` | Immutable user rating aggregate per video execution version |
| `UserPreferenceProfile` | Deterministic preferences derived from averaged review scores |
| `SaveReviewHandler` | Persists reviews and rebuilds preference profile |
| `BuildPreferenceProfileHandler` | Aggregates reviews into explainable preferences |
| `DeterministicPipelinePlanner` | Applies preferences to strategy and provider selection |
| `ReviewPanel` | Star ratings, comments, and save action in workspace UI |
| `ReviewService` | Repository-backed review and preference operations |

Feature components must use `reviewService`, not `HttpReviewRepository` or `HttpClient` directly.

## Platform Sprint 48 — Team Collaboration & Shared Workspaces

```text
Organization → Workspace → Members → Projects → Videos
```

| Component | Role |
| --------- | ---- |
| `WorkspaceRole` | Owner, Editor, Reviewer, Viewer permission model |
| `WorkspaceMember` | Immutable membership aggregate |
| `WorkspaceInvitation` | Deterministic pending invite with expiry |
| `InviteWorkspaceMemberHandler` | Owner-only invitations |
| `WorkspaceAuthorizationService` | Role checks on workspace actions |
| `TeamPanel` | Member list, invite form, role management UI |
| `CollaborationService` | Repository-backed team operations |

Feature components must use `collaborationService`, not `HttpCollaborationRepository` or `HttpClient` directly.

## Platform Sprint 49 — Observability, Monitoring & Analytics

```text
Pipeline → Telemetry Collector → Metrics Repository → Analytics Engine → Dashboard
```

| Component | Role |
| --------- | ---- |
| `ExecutionMetricType` | Processing, queue, CPU/GPU, memory, success rate, retry metrics |
| `PipelineTelemetry` | Immutable append-only execution telemetry aggregate |
| `CollectPipelineMetricsHandler` | Persists telemetry without blocking processing |
| `WorkspaceAnalyticsAggregator` | Deterministic workspace-level aggregation |
| `PipelineTelemetryRecorder` | Runtime instrumentation from `ProcessVideoHandler` |
| `AnalyticsDashboard` | Workspace analytics UI |
| `TelemetryService` | Repository-backed analytics operations |

Feature components must use `telemetryService`, not `HttpTelemetryRepository` or `HttpClient` directly.

## Product read model (Sprint 50.5)

`WorkItem` is a **frontend product read model** — not a backend domain merge.

| Rule | Detail |
| ---- | ------ |
| Location | `frontend/src/services/workItem/` |
| Purpose | Unify Home, navigation, and recent work across Content, Video, Project |
| Do not | Replace or merge `Content` / `Video` backend aggregates |
| Do not | Invent actions without backend support |
| Must | Every WorkItem exposes a valid `openRoute` |

Feature components (Home, Recent Work, sidebar) must use `workItemService`, not `HttpWorkItemRepository` or raw Content/Video HTTP calls for list/navigation UX.

Video hub: `/video/:videoId` is the overview entry; step routes (`/transcript`, `/render`, …) remain detail pages.

See [PRODUCT_INFORMATION_ARCHITECTURE.md](./PRODUCT_INFORMATION_ARCHITECTURE.md).

## Source processing platform (Sprint 51–52)

`Source` is the **ingestion domain** above legacy `Content` and `Video` aggregates.

| Rule | Detail |
| ---- | ------ |
| Location | `backend/src/Domain/Source/` |
| Connectors | Audio (`POST /api/audio`), YouTube (`POST /api/youtube`) |
| Do not | Merge `Content` / `Video` domains |
| Audio pipeline | Reuse STT + translation; skip lip-sync/render for audio |
| YouTube pipeline | Download → `VideoJob` → **full existing video pipeline** |
| Do not | Create `YouTubeTranscript`, `YouTubeTranslation`, etc. |

YouTube domain: `backend/src/Domain/YouTube/`. Importer port: `YouTubeImporterInterface` (yt-dlp in Docker, mock in tests).

See [SOURCE_PROCESSING_PLATFORM.md](./SOURCE_PROCESSING_PLATFORM.md).

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
