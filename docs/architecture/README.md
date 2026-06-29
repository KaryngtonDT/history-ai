# Architecture Documentation

Version: 1.0

Status: Active

---

# Purpose

This folder records **Architecture Decision Records (ADRs)** for History AI — the major structural choices made during Sprints 1–10.

ADRs complement:

| Artifact | Location | Role |
| -------- | -------- | ---- |
| RFC | `docs/06_RFC/` | Proposal and debate before a decision |
| ADR | `docs/architecture/` (here) | Frozen record of what was decided and why |
| Blueprint | `docs/02_ARCHITECTURE/SYSTEM_BLUEPRINT.md` | How the system is organized in code |
| Engineering principles | `engineering/00_ENGINEERING_PRINCIPLES.md` | Immutable rules |

See also `docs/05_DECISIONS/README.md` for the formal RFC → ADR workflow.

---

# What is an ADR?

An ADR captures a **single architectural decision** at a point in time:

1. **Context** — the problem or constraint.
2. **Decision** — what we chose.
3. **Alternatives considered** — what we rejected.
4. **Consequences** — trade-offs (positive and negative).

ADRs are **immutable once accepted**. If a decision changes, add a new ADR that supersedes the old one.

---

# Numbering convention

```text
ADR-NNNN-short-title.md
```

| Part | Rule |
| ---- | ---- |
| `NNNN` | Four-digit zero-padded sequence (`0001`, `0002`, …) |
| `short-title` | Lowercase kebab-case summary |
| Status | `Accepted`, `Proposed`, or `Superseded by ADR-XXXX` |

When adding a new ADR:

1. Read existing ADRs to avoid duplication.
2. Use the next available number.
3. Set status to `Proposed` during review, then `Accepted`.
4. Link related RFCs and blueprint sections.
5. Update the index table below.

---

# Index

| ADR | Title | Status |
| --- | ----- | ------ |
| [ADR-0001](ADR-0001-clean-architecture.md) | Clean Architecture (backend layers) | Accepted |
| [ADR-0002](ADR-0002-ai-provider.md) | AI Provider abstraction (worker) | Accepted |
| [ADR-0003](ADR-0003-artifact-pipeline.md) | Extensible artifact generation pipeline | Accepted |
| [ADR-0004](ADR-0004-library-domain.md) | Library as a separate bounded context | Accepted |
| [ADR-0005](ADR-0005-collections.md) | Collections via junction aggregate | Accepted |

See [architecture-rules.md](./architecture-rules.md) for automated dependency enforcement.

See [ci.md](./ci.md) for the GitHub Actions pipeline.

See [openapi.md](./openapi.md) for OpenAPI / Swagger UI documentation (includes `GET /api/timeline/{artifactId}` since Sprint 14, `GET /api/maps/timeline/{artifactId}` since Sprint 15, `GET /api/contents/{contentId}/relations` since Sprint 16, and `GET /api/contents/{contentId}/graph` since Sprint 17).

---

# Sprint 14 — Interactive Timeline (2026-06)

Sprint 14 extended the Sprint 13 timeline artifact with:

| Layer | Addition |
| ----- | -------- |
| Domain | `Timeline`, `TimelineSection`, `TimelineEvent` + `TimelineParser` |
| Backend API | `GET /api/timeline/{artifactId}` → structured JSON projection |
| Frontend | `TimelineService`, `InteractiveTimeline`, markdown fallback |
| OpenAPI | `Timeline`, `TimelineSection`, `TimelineEvent` schemas |
| Architecture | Timeline layer rules (backend + frontend transport guards) |

Verification: [Sprint14-Verification.md](../reports/Sprint14-Verification.md)

---

# Sprint 15 — Interactive Historical Map (2026-06)

Sprint 15 extended the Sprint 14 timeline with geographic place resolution:

| Layer | Addition |
| ----- | -------- |
| Domain | `HistoricalPlace`, `Coordinates`, `HistoricalPlaceCollection`, `TimelinePlaceResolver` |
| Backend API | `GET /api/maps/timeline/{artifactId}` → map JSON projection |
| Frontend | `MapService`, `TimelineMapPanel`, `InteractiveMap` (CSS-only map layout) |
| OpenAPI | `Map`, `HistoricalPlace`, `Coordinates` schemas |
| Architecture | Map layer rules (backend + frontend transport guards) |

Verification: [Sprint15-Verification.md](../reports/Sprint15-Verification.md)

---

# Sprint 16 — Artifact Relations (2026-06)

Sprint 16 connected learning artifacts within a content into a deterministic relation graph:

| Layer | Addition |
| ----- | -------- |
| Domain | `ArtifactRelation`, `ArtifactRelationCollection`, `ArtifactRelationType`, `ArtifactRelationResolver` |
| Backend API | `GET /api/contents/{contentId}/relations` → relations JSON projection |
| Frontend | `RelationService`, `ArtifactRelationsPanel` on Processing page |
| OpenAPI | `ArtifactRelation`, `ArtifactRelations`, `ArtifactRelationType` schemas |
| Architecture | Relation layer rules (backend + frontend transport guards) |

Verification: [Sprint16-Verification.md](../reports/Sprint16-Verification.md)

---

# Sprint 17 — Knowledge Graph (2026-06)

Sprint 17 projected artifact relations into a navigable knowledge graph:

| Layer | Addition |
| ----- | -------- |
| Domain | `GraphNode`, `GraphEdge`, `KnowledgeGraph`, `KnowledgeGraphBuilder` |
| Backend API | `GET /api/contents/{contentId}/graph` → knowledge graph JSON projection |
| Frontend | `GraphService`, `KnowledgeGraphPanel`, `InteractiveGraph` (CSS-only layout) |
| OpenAPI | `KnowledgeGraph`, `GraphNode`, `GraphEdge` schemas |
| Architecture | Graph layer rules (backend + frontend transport guards) |

Verification: [Sprint17-Verification.md](../reports/Sprint17-Verification.md)

---

# Sprint 18 — Contextual Recommendations (2026-06)

Sprint 18 delivered contextual “See also” recommendations powered by the knowledge graph:

| Layer | Addition |
| ----- | -------- |
| Domain | `RecommendationEngine`, `RecommendedArtifact`, `RecommendedArtifactCollection`, `RecommendationReason` |
| Backend API | `GET /api/contents/{contentId}/artifacts/{artifactId}/recommendations` → recommendations JSON projection |
| Frontend | `RecommendationService`, `SeeAlsoRecommendationsPanel` under each artifact card |
| OpenAPI | `RecommendedArtifact`, `ArtifactRecommendations`, `RecommendationReason` schemas |
| Architecture | Recommendation layer rules (backend + frontend transport guards) |

Verification: [Sprint18-Verification.md](../reports/Sprint18-Verification.md)

---

# Sprint 19 — Recommendation Scoring (2026-06)

Sprint 19 enriched contextual recommendations with relevance scoring end-to-end:

| Layer | Addition |
| ----- | -------- |
| Domain | `RecommendationScoringEngine`, `RecommendationScore`, `RecommendationWeight`, `ScoredRecommendation`, `ScoredRecommendationCollection` |
| Backend API | `score` field on each recommendation in `GET /api/contents/{contentId}/artifacts/{artifactId}/recommendations` (sorted by score descending) |
| Frontend | Score mapping in `RecommendationService` layer; relevance badge in `SeeAlsoRecommendationsPanel` (`"80% relevant"`) |
| OpenAPI | `score` on `RecommendedArtifact` schema (integer 0–100) |
| Architecture | Existing recommendation layer rules unchanged |

Verification: [Sprint19-Verification.md](../reports/Sprint19-Verification.md)

---

# Sprint 20 — Semantic Search (2026-06)

Sprint 20 delivered semantic chunk retrieval end-to-end: chunking domain, embedding abstraction, deterministic embeddings, in-memory retriever, semantic search API, frontend service, and UI panel. Slice 8 changed **documentation and OpenAPI only** — no business logic in backend, frontend, or worker.

| Layer | Addition |
| ----- | -------- |
| Domain | `Chunker`, `Chunk`, `EmbeddingVector`, `EmbeddedChunk`, `EmbeddingGeneratorInterface`, `SemanticRetriever`, `SemanticQuery`, `SimilarityScore`, `RetrievedChunk` |
| Infrastructure | `DeterministicEmbeddingGenerator` (hash-based, dim 8) |
| Backend API | `GET /api/contents/{contentId}/semantic-search?q=…` → semantic search JSON projection |
| Frontend | `SemanticSearchService`, `SemanticSearchPanel`, `SemanticSearchResults` |
| OpenAPI | `RetrievedChunk`, `SemanticSearchResult` schemas |
| Architecture | Semantic layer rules (backend + frontend transport guards) |

Verification: [Sprint20-Verification.md](../reports/Sprint20-Verification.md)

---

# Sprint 21 — Vector Store (2026-06)

Sprint 21 introduced a **Vector Store abstraction** and refactored semantic retrieval to route through it. Slice 4 changed **documentation and verification only** — no business logic in backend, frontend, or worker.

| Layer | Addition |
| ----- | -------- |
| Domain | `VectorDocument`, `VectorDocumentCollection`, `VectorSearchResult`, `VectorSearchResultCollection`, `VectorStoreInterface` |
| Infrastructure | `InMemoryVectorStore` (cosine similarity, top-K, replace-on-index) |
| Application | `SearchSemanticChunksHandler` indexes `VectorDocumentCollection` before retrieval |
| Domain (refactor) | `SemanticRetriever` delegates search to `VectorStoreInterface`; cosine logic removed from retriever |
| API / Frontend / Worker | Unchanged — semantic-search contract and UI preserved |

Verification: [Sprint21-Verification.md](../reports/Sprint21-Verification.md)

---

# Sprint 22 — Real Embedding Provider (2026-06)

Sprint 22 introduced a **multi-provider embedding architecture** with config-driven selection and an optional Gemini adapter. Slice 5 changed **documentation and verification only** — no business logic in backend, frontend, or worker.

| Layer | Addition |
| ----- | -------- |
| Domain | `EmbeddingProviderInterface` — port for single-text embedding generation |
| Infrastructure | `DeterministicEmbeddingProvider` (SHA-256); `GeminiEmbeddingProvider` (Gemini `embedContent`); `EmbeddingProviderFactory`; `GeminiEmbeddingTransportInterface` |
| Refactor | `DeterministicEmbeddingGenerator` delegates to `EmbeddingProviderInterface` |
| Console | `semantic:embedding:smoke-test` — manual Gemini verification (not CI) |
| API / Frontend / Worker | Unchanged — semantic-search contract preserved |

Provider selection via `EMBEDDING_PROVIDER` env var (`deterministic` default, `gemini` requires `GEMINI_API_KEY`). Test/CI env keeps `EMBEDDING_PROVIDER=deterministic`.

Verification: [Sprint22-Verification.md](../reports/Sprint22-Verification.md)

---

# UX-01 — Chat RAG (2026-06)

UX-01 delivers an interactive RAG chat experience: backend retrieval + provider abstraction, frontend `ChatPanel`, and OpenAPI documentation for `POST /api/contents/{contentId}/chat`.

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| UX-01-SLICE-01 | Domain chat model (`ChatOrchestrator`, `ChatProviderInterface`) | ✅ |
| UX-01-SLICE-02 | Mock RAG chat API (`POST /api/contents/{contentId}/chat`) | ✅ |
| UX-01-SLICE-03 | Generalized `ChatRequest` / `ChatResponse` provider contract | ✅ |
| UX-01-SLICE-04 | Optional `GeminiChatProvider` adapter | ✅ |
| UX-01-SLICE-05 | `ChatProviderFactory`; `CHAT_PROVIDER` env selection | ✅ |
| UX-01-SLICE-06 | Frontend `ChatService` + repository layer | ✅ |
| UX-01-SLICE-07 | Frontend `ChatPanel` UI in `ProcessingArtifacts` | ✅ |
| UX-01-SLICE-08 | OpenAPI schemas + UX-01 verification report | ✅ |

| Layer | Addition |
| ----- | -------- |
| Domain | `ChatProviderInterface` with `ChatRequest` / `ChatResponse`; `ChatProviderOptions` (temperature, maxTokens, model) |
| Infrastructure | `MockChatProvider` (default); `GeminiChatProvider`; `ChatProviderFactory`; `GeminiChatTransportInterface`; `CurlGeminiChatTransport` |
| Application | `AskContentChatHandler` builds `ChatRequest`, maps `ChatResponse` to DTO |
| Presentation | OpenAPI schemas `ChatRequest`, `ChatAnswer`, `ChatSource`; `#[OA\Post]` on chat controller |
| Frontend | `ChatService`; `ChatPanel` + props-only subcomponents; architecture guard `feature-chat-transport` |

Chat provider selection via `CHAT_PROVIDER` env var (`mock` default, `gemini` requires `GEMINI_API_KEY`). Test/CI env keeps `CHAT_PROVIDER=mock`. Gemini env vars: `GEMINI_API_KEY`, `GEMINI_CHAT_MODEL` (default `gemini-2.5-flash`). Tests use mocked transport; no live API calls in CI.

Verification: [UX01-Verification.md](../reports/UX01-Verification.md)

---

# UX-02 — Interactive Citations (2026-06)

UX-02 adds **numbered, navigable citations** to the RAG chat experience: domain `ChatCitation` model, API `citations[]` field, frontend mapping, and click-to-scroll highlight in `ProcessingArtifacts`.

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| UX-02-SLICE-01 | Domain `ChatCitation`, `ChatCitationCollection`; `ChatResponse` enriched | ✅ |
| UX-02-SLICE-02 | Application DTO + JSON `citations[]` on chat API | ✅ |
| UX-02-SLICE-03 | Frontend citation mapping (`ChatService` layer) | ✅ |
| UX-02-SLICE-04 | Interactive navigation (`[1]` click → scroll + highlight) | ✅ |
| UX-02-SLICE-05 | OpenAPI `ChatCitation` schema + UX-02 verification report | ✅ |

| Layer | Addition |
| ----- | -------- |
| Domain | `ChatCitation`, `ChatCitationCollection`; mock provider emits `[1]` markers |
| Application | `ChatCitationResult`; `ChatAnswerResult.citations[]` |
| Presentation | OpenAPI schemas `ChatCitation`; `ChatAnswer.citations[]` |
| Frontend | `ChatCitation` type; clickable markers in `ChatMessage`; `citationNavigation.ts` |

Citations omit `text` in JSON — frontend resolves `citation.chunkId` against `sources[]` for excerpt text.

Verification: [UX02-Verification.md](../reports/UX02-Verification.md)

---

# UX-03 — Streaming Chat (2026-06)

UX-03 adds **progressive streaming answers** to the RAG chat experience: domain stream model, provider interface, mock SSE endpoint, frontend SSE service, and progressive UI in `ChatPanel`.

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| UX-03-SLICE-01 | Domain `ChatToken`, `ChatStream`, `ChatStreamEvent`, collections | ✅ |
| UX-03-SLICE-02 | `StreamingChatProviderInterface`; `MockChatProvider` streamable | ✅ |
| UX-03-SLICE-03 | Mock SSE endpoint `POST /chat/stream` | ✅ |
| UX-03-SLICE-04 | Frontend `ChatService.streamQuestion()` + SSE parsing | ✅ |
| UX-03-SLICE-05 | Progressive assistant bubble in `ChatPanel` | ✅ |
| UX-03-SLICE-06 | OpenAPI `ChatStreamToken` + UX-03 verification report | ✅ |

| Layer | Addition |
| ----- | -------- |
| Domain | `ChatToken`, `ChatStream`, `ChatStreamEvent`; `toAnswer()` for aggregation |
| Application | `AskContentChatStreamHandler`; `ChatStreamResult` DTOs |
| Presentation | SSE `ChatStreamResponse`; OpenAPI on stream controller |
| Infrastructure | `MockChatProvider::stream()`; DI `StreamingChatProviderInterface` |
| Frontend | `HttpChatRepository.streamQuestion()` (fetch + SSE); progressive UI |

Non-streaming `POST /chat` unchanged — full answer with sources and citations.

Verification: [UX03-Verification.md](../reports/UX03-Verification.md)

---

# Platform Sprint 23 — Observability & Performance (2026-06)

Platform Sprint 23 hardened cross-cutting platform concerns: correlation IDs, performance metrics, an internal metrics API, and embedding cache. Slice 5 changed **documentation and OpenAPI only** — no business logic in backend handlers, frontend, or worker.

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| P23-SLICE-01 | `CorrelationId`, `RequestContext`, `X-Correlation-ID` header, structured logging | ✅ |
| P23-SLICE-02 | `PerformanceTimer`, `PerformanceMetric`, RAG pipeline timings | ✅ |
| P23-SLICE-03 | `InMemoryPerformanceMetricsStore`; `GET /internal/platform/metrics` | ✅ |
| P23-SLICE-04 | `EmbeddingCacheInterface`, `CachedEmbeddingProvider` (LRU, max 1000) | ✅ |
| P23-SLICE-05 | OpenAPI `PerformanceMetric*`, architecture docs, this report | ✅ |

| Layer | Addition |
| ----- | -------- |
| Domain | `CorrelationId`; `PerformanceMetric`, `PerformanceMetricCollection`; `EmbeddingCacheKey`, `EmbeddingCacheInterface` |
| Application | `RequestContext`, `PerformanceTimer`, `PerformanceMetricsRecorderInterface`, `PerformanceMetricsReaderInterface` |
| Infrastructure | `RequestCorrelationIdListener`, `PlatformLogger`, `LoggingPerformanceMetricsRecorder`, `InMemoryPerformanceMetricsStore`, `CompositePerformanceMetricsRecorder`, `CachedEmbeddingProvider`, `InMemoryEmbeddingCache` |
| Presentation | `GET /internal/platform/metrics`; OpenAPI schemas `PerformanceMetric`, `PerformanceMetricSnapshot`, `PlatformMetricsResponse` |

Handlers instrumented: `SearchSemanticChunksHandler`, `AskContentChatHandler`, `AskContentChatStreamHandler`. Metrics captured: `chunking_ms`, `embedding_ms`, `vector_index_ms`, `retrieval_ms`, `provider_ms`, `total_ms`.

Verification: [Platform23-Verification.md](../reports/Platform23-Verification.md)

---

# Platform Sprint 24 — Conversation Memory (2026-06)

Platform Sprint 24 delivers **persistent multi-turn chat** attached to a content resource: domain model, Doctrine repository, conversation-aware API, frontend integration, and OpenAPI documentation. Slice 5 changed **documentation and OpenAPI only** — no business logic in backend handlers, frontend, or worker.

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| P24-SLICE-01 | `ConversationId`, `Conversation`, `ConversationCollection`; immutable append | ✅ |
| P24-SLICE-02 | `ConversationRepositoryInterface`, `DoctrineConversationRepository`, migration | ✅ |
| P24-SLICE-03 | `AskConversationChatHandler`; `POST …/conversations/{conversationId}/chat` | ✅ |
| P24-SLICE-04 | Frontend `ConversationService`; `ChatPanel` uses server `conversation.messages` | ✅ |
| P24-SLICE-05 | OpenAPI `Conversation*` schemas, architecture docs, this report | ✅ |

| Layer | Addition |
| ----- | -------- |
| Domain | `ConversationId`, `Conversation`, `ChatConversation`, `ConversationRepositoryInterface` |
| Application | `AskConversationChatHandler`; `ConversationChatResult`, `ConversationResult` DTOs |
| Infrastructure | `DoctrineConversationRepository`, `ConversationRecord` (JSON messages) |
| Presentation | `AskConversationChatController`; OpenAPI schemas `Conversation`, `ConversationMessage`, `ConversationChatResponse` |
| Frontend | `ConversationService`; `ChatPanel` state from `conversation.messages`; `streamQuestion()` preserved but unused |

```text
ChatPanel → ConversationService → POST /conversations/{id}/chat
        → ConversationRepository → AskContentChatHandler (RAG) → ChatProvider
        → Conversation JSON → frontend renders conversation.messages
```

Verification: [Sprint24-Verification.md](../reports/Sprint24-Verification.md)

---

# Platform Sprint 25 — Multi-Document RAG (2026-06)

Platform Sprint 25 delivers **multi-document conversations**: domain model, Doctrine persistence, RAG across selected documents, document selection API, frontend `DocumentSelector`, and OpenAPI documentation. Slice 5 changed **documentation and OpenAPI only** — no business logic in backend handlers, frontend, or worker.

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| P25-SLICE-01 | `SelectedDocument`, `SelectedDocumentCollection`; multi-doc `Conversation` domain | ✅ |
| P25-SLICE-02 | `documents` JSON column; `DoctrineConversationRepository` multi-doc | ✅ |
| P25-SLICE-03 | `ContentChatAnswerer`; RAG across all selected documents | ✅ |
| P25-SLICE-04A | `PUT /api/conversations/{conversationId}/documents` | ✅ |
| P25-SLICE-04B | Frontend `DocumentSelector`; `ConversationService.updateDocuments()` | ✅ |
| P25-SLICE-05 | OpenAPI multi-doc schemas, architecture docs, this report | ✅ |

| Layer | Addition |
| ----- | -------- |
| Domain | `SelectedDocument`, `SelectedDocumentCollection`; `Conversation::withDocuments()` |
| Application | `UpdateConversationDocumentsHandler`; `SelectedDocumentResult`; `documents[]` on `ConversationResult` |
| Infrastructure | `documents` JSON on `ConversationRecord`; multi-doc repository queries |
| Presentation | `UpdateConversationDocumentsController`; OpenAPI `SelectedDocument`, `UpdateConversationDocumentsRequest`, `ConversationResponse` |
| Frontend | `DocumentSelector`; `ChatPanel` document selection via `updateDocuments()` |

```text
ChatPanel → DocumentSelector → ConversationService.updateDocuments()
        → PUT /conversations/{id}/documents → conversation.documents[]
        → POST /contents/{contentId}/conversations/{id}/chat
        → RAG over all selected documents
```

Verification: [Sprint25-Verification.md](../reports/Sprint25-Verification.md)

---

# Platform Sprint 26 — Conversation Streaming (2026-06)

Platform Sprint 26 delivers **conversation-aware streaming chat**: domain stream model, SSE API with persistence, frontend `ChatPanel` integration, and OpenAPI documentation. Slice 4 changed **documentation and OpenAPI only** — no business logic in backend handlers, frontend, or worker.

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| P26-SLICE-01 | `ConversationStream`, `ConversationStreamEvent` domain | ✅ |
| P26-SLICE-02 | `POST …/conversations/{conversationId}/chat/stream` SSE API | ✅ |
| P26-SLICE-03 | `ConversationService.streamQuestion()`; `ChatPanel` streaming UX | ✅ |
| P26-SLICE-04 | OpenAPI + architecture docs + this report | ✅ |

| Layer | Addition |
| ----- | -------- |
| Domain | `ConversationStream`, `ConversationStreamEvent`, `ConversationStreamEventCollection` |
| Application | `AskConversationChatStreamHandler`; `ContentChatStreamer`; stream DTOs |
| Presentation | `AskConversationChatStreamController`; `ConversationChatStreamResponse`; OpenAPI `ConversationStreamEvent` |
| Frontend | `ConversationService.streamQuestion()`; optimistic tokens + `conversation` event as source of truth |

```text
ChatPanel → ConversationService.streamQuestion()
        → POST /contents/{contentId}/conversations/{id}/chat/stream
        → SSE token → conversation → done
        → frontend conversation.messages = backend source of truth
```

Verification: [Sprint26-Verification.md](../reports/Sprint26-Verification.md)

---

# Platform Sprint 27 — Knowledge Graph Explorer 2.0 (2026-06)

Platform Sprint 27 extends the Sprint 17 knowledge graph with interactive neighborhood exploration and conversation-scoped graph projections. Slice 5 changed **OpenAPI documentation, architecture docs, and verification only** — no business logic in backend handlers, frontend, or worker.

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| P27-SLICE-01 | Graph domain collections, `GraphNeighborhood`, `neighborsOf()` | ✅ |
| P27-SLICE-02 | `GET …/graph/artifacts/{artifactId}/neighborhood` | ✅ |
| P27-SLICE-03 | `GraphService.getGraphNeighborhood()`; interactive `KnowledgeGraphPanel` | ✅ |
| P27-SLICE-04 | `GET /api/conversations/{conversationId}/graph` | ✅ |
| P27-SLICE-05 | OpenAPI + architecture docs + this report | ✅ |

| Layer | Addition |
| ----- | -------- |
| Domain | `GraphNodeCollection`, `GraphEdgeCollection`, `GraphNeighborhood`, `neighborsOf()` |
| Backend API | Neighborhood endpoint; conversation-scoped graph endpoint |
| Frontend | `getGraphNeighborhood()`, `getConversationGraph()`; node highlight UX |
| OpenAPI | `GraphNeighborhood`, `GraphNeighborhoodNode`; `GraphEdge.weight` |

```text
KnowledgeGraphPanel
        │
        ▼
GraphService
        ├── GET /contents/{contentId}/graph
        ├── GET /contents/{contentId}/graph/artifacts/{artifactId}/neighborhood
        └── GET /conversations/{conversationId}/graph
        │
        ▼
InteractiveGraph (selected / neighbors / edges highlight)
```

Verification: [Sprint27-Verification.md](../reports/Sprint27-Verification.md)

---

# Platform Sprint 28 — Agent Workflows (2026-06)

Platform Sprint 28 delivers deterministic agent planning, execution trace projection, HTTP API, frontend agent mode, and OpenAPI documentation. Slice 5 changed **OpenAPI documentation, architecture docs, and verification only** — no business logic in backend handlers, frontend, or worker.

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| P28-SLICE-01 | Agent domain (`AgentTool`, `AgentPlan`, `AgentStep`, collections) | ✅ |
| P28-SLICE-02 | `DeterministicAgentPlanner`, keyword-based plan expansion | ✅ |
| P28-SLICE-03 | `RunAgentHandler`, execution trace DTOs (no real tool calls) | ✅ |
| P28-SLICE-04 | `POST …/agent/run`; `AgentModePanel` + execution trace UI | ✅ |
| P28-SLICE-05 | OpenAPI + architecture docs + this report | ✅ |

| Layer | Addition |
| ----- | -------- |
| Domain | `AgentTool`, `AgentPlan`, `AgentStep`, `AgentExecutionResult`, status enum |
| Application | `RunAgentHandler`; plan + steps + `finalSummary` DTOs |
| Infrastructure | `DeterministicAgentPlanner` (comparison/memory keywords) |
| Backend API | `POST /api/contents/{contentId}/agent/run` |
| Frontend | `AgentService`, `AgentModePanel`, `AgentExecutionTrace` |
| OpenAPI | `AgentRunRequest`, `AgentExecution`, `AgentTool`, `AgentExecutionStatus` |

```text
AgentModePanel
        │
        ▼
AgentService.runAgent()
        │
        ▼
POST /api/contents/{contentId}/agent/run
        │
        ▼
RunAgentHandler → DeterministicAgentPlanner
        │
        ▼
AgentExecution (plan[], steps[], finalSummary)
        │
        ▼
AgentExecutionTrace UI
```

Verification: [Sprint28-Verification.md](../reports/Sprint28-Verification.md)

---

# Platform Sprint 29 — Real Tool Execution (2026-06)

Platform Sprint 29 wires **real agent tool execution** through `AgentToolExecutorInterface`. Three tools delegate to existing Application handlers; `ConversationMemory` remains a no-op stub. Slice 5 changed **OpenAPI documentation, architecture docs, and verification only** — no business logic in backend handlers, frontend, or worker.

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| P29-SLICE-01 | `AgentToolExecution`, `AgentToolExecutionResult`, `AgentToolExecutorInterface`, `NullAgentToolExecutor` | ✅ |
| P29-SLICE-02 | `SemanticSearchToolExecutor` → `SearchSemanticChunksHandler` | ✅ |
| P29-SLICE-03 | `KnowledgeGraphToolExecutor` → `GetKnowledgeGraphHandler` | ✅ |
| P29-SLICE-04 | `MultiDocumentChatToolExecutor` → `AskConversationChatHandler` | ✅ |
| P29-SLICE-05 | OpenAPI metadata, architecture docs, this report | ✅ |

| Layer | Addition |
| ----- | -------- |
| Domain | `AgentToolExecution`, `AgentToolExecutionResult`, `AgentToolExecutorInterface`; `AgentExecutionStep.metadata` |
| Application | `RunAgentHandler` delegates each step to `AgentToolExecutorInterface`; continue-on-failure policy |
| Infrastructure | `CompositeAgentToolExecutor`, `SemanticSearchToolExecutor`, `KnowledgeGraphToolExecutor`, `MultiDocumentChatToolExecutor`, `NullAgentToolExecutor` |
| OpenAPI | `AgentExecutionStep.metadata` documented with tool-specific examples |

```text
AgentModePanel
        │
        ▼
AgentService.runAgent()
        │
        ▼
POST /api/contents/{contentId}/agent/run
        │
        ▼
RunAgentHandler → AgentPlannerInterface
        │
        ▼
CompositeAgentToolExecutor
        ├── SemanticSearchToolExecutor      ✅ real
        ├── KnowledgeGraphToolExecutor      ✅ real
        ├── MultiDocumentChatToolExecutor     ✅ real
        └── NullAgentToolExecutor           ❌ memory stub
        │
        ▼
AgentExecutionResult + step metadata
```

Verification: [Sprint29-Verification.md](../reports/Sprint29-Verification.md)

---

# Project architecture overview

History AI is a **modular monolith** with three runtime applications and a shared domain story:

```mermaid
flowchart TB
    subgraph Frontend["Frontend (React)"]
        Pages["Pages / Features"]
        Services["Domain Services"]
        Repos["Repository (Http / Mock)"]
        Pages --> Services --> Repos
    end

    subgraph Backend["Backend (Symfony)"]
        Presentation["Presentation (REST)"]
        Application["Application (CQRS Handlers)"]
        Domain["Domain (Aggregates, VOs, Ports)"]
        Infrastructure["Infrastructure (Doctrine, etc.)"]
        Presentation --> Application --> Domain
        Infrastructure --> Domain
    end

    subgraph Worker["Worker (FastAPI)"]
        ProcessingService["ProcessingService"]
        Generators["Artifact Generators"]
        AIProvider["AIProviderInterface"]
        ProcessingService --> Generators --> AIProvider
    end

    Repos -->|HTTP| Presentation
    Worker -->|HTTP| Presentation
    Infrastructure --> Postgres[(PostgreSQL)]
```

**Dependency rule (backend):** outer layers depend on inner layers. Domain depends on nothing infrastructure-specific.

**Dependency rule (frontend):** UI features call services only. HTTP is confined to `HttpClient` + repository implementations.

**Dependency rule (worker):** processing orchestration depends on generator and provider interfaces, not concrete AI vendors.

---

# Domain model (Sprint 10)

```text
Content
  └── ProcessingJob
        └── Artifact (transcript, summary, quiz, flashcards, timeline, podcast, …)
              └── LibraryItem (saved artifact reference)
                    └── CollectionItem (many-to-many via junction)
                          └── Collection
```

Each bounded context follows the same vertical slice:

Domain → Repository Port → Doctrine Adapter → Application (CQRS) → REST API → Frontend Service → UI.

---

# Related documentation

- [SYSTEM_BLUEPRINT.md](../02_ARCHITECTURE/SYSTEM_BLUEPRINT.md)
- [RFC-0001 Content Processing Pipeline](../06_RFC/RFC-0001-content-processing-pipeline.md)
- [Engineering Principles](../../engineering/00_ENGINEERING_PRINCIPLES.md)
- [Frontend Repository Pattern](../frontend/Repository%20Pattern.md)
