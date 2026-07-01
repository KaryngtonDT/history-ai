# Architecture Documentation

Version: 1.0

Status: Active

---

# Purpose

This folder records **Architecture Decision Records (ADRs)** for **Lumen** (public product name; repository identifiers may still use `history-ai`) — the major structural choices made during Sprints 1–10.

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

# Platform Sprint 30 — Agent Metadata & Conversation Memory (2026-06)

Platform Sprint 30 completes the agent tool stack: **Conversation Memory** executes against `ConversationRepositoryInterface`, **metadata aggregation** merges per-step tool metadata into `AgentExecutionResult.metadata`, and the frontend **AgentMetadataPanel** surfaces tool metrics in the agent trace UI. Slice 5 changed **OpenAPI documentation, architecture docs, and verification only** — no business logic in backend handlers, frontend, or worker.

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| P30-SLICE-01 | `ConversationMemoryExecution`, `ConversationMemoryResult`, `ConversationMemoryToolExecutorInterface`, `NullConversationMemoryToolExecutor` | ✅ |
| P30-SLICE-02 | `ConversationMemoryToolExecutor`, composite routing | ✅ |
| P30-SLICE-03 | `AgentMetadata`, `AgentMetadataCollection`, aggregated `AgentExecutionResult.metadata` | ✅ |
| P30-SLICE-04 | HTTP `metadata` serialization; `AgentMetadataPanel` UI | ✅ |
| P30-SLICE-05 | OpenAPI top-level metadata, architecture docs, this report | ✅ |

| Layer | Addition |
| ----- | -------- |
| Domain | `ConversationMemoryExecution`, `ConversationMemoryResult`, `ConversationMemoryToolExecutorInterface`; `AgentMetadata`, `AgentMetadataCollection` |
| Application | `RunAgentHandler` aggregates step metadata; `AgentExecutionResultDto.metadata` |
| Infrastructure | `ConversationMemoryToolExecutor`, `ConversationMemoryAgentToolExecutor`; composite routes all four tools |
| Presentation | `AgentExecutionResponse` exposes `metadata` and `steps[].metadata`; OpenAPI `AgentExecution.metadata` |
| Frontend | `AgentMetadataPanel`, `agentMetadataLabels`; types map API metadata |

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
        ├── ConversationMemoryToolExecutor  ✅ real
        └── MultiDocumentChatToolExecutor   ✅ real
        │
        ▼
AgentMetadataCollection.merge()
        │
        ▼
AgentExecution (plan[], steps[], finalSummary, metadata)
        │
        ▼
AgentExecutionTrace + AgentMetadataPanel
```

Verification: [Sprint30-Verification.md](../reports/Sprint30-Verification.md)

---

# Platform Sprint 31 — Video Processing Foundation (2026-06)

Platform Sprint 31 delivers the **video upload foundation** for the AI Video Localization Platform (Phase 2): domain model, multipart upload API, local storage, Doctrine persistence, queue dispatch, frontend upload UI, and OpenAPI documentation. Slice 5 changed **OpenAPI documentation, architecture docs, and verification only** — no business logic in backend handlers, frontend, or worker.

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| P31-SLICE-01 | `VideoJob`, `VideoId`, `VideoStatus`, `VideoLanguage`, `VideoJobCollection` | ✅ |
| P31-SLICE-02 | `POST /api/videos` multipart upload endpoint | ✅ |
| P31-SLICE-03 | `LocalVideoStorage`, `DoctrineVideoRepository`, `ProcessVideoMessage` queue | ✅ |
| P31-SLICE-04 | `VideoUploadPanel`, `VideoService`, upload progress UI | ✅ |
| P31-SLICE-05 | OpenAPI video schemas, architecture docs, this report | ✅ |

| Layer | Addition |
| ----- | -------- |
| Domain | `VideoJob` aggregate, lifecycle transitions, `VideoExtension`, `VideoUploadSize` |
| Application | `UploadVideoHandler`, `VideoStorageInterface`, `VideoProcessingQueueInterface`, `ProcessVideoMessage` |
| Infrastructure | `LocalVideoStorage`, `DoctrineVideoRepository`, `MessengerVideoProcessingQueue` |
| Presentation | `UploadVideoController`, OpenAPI `UploadVideoResponse` / `VideoStatus` |
| Frontend | `VideoUploadPanel`, `VideoService`, `HttpClient.postFormData()` for progress |

```text
Frontend VideoUploadPanel
        │
        ▼
POST /api/videos (multipart, field: video)
        │
        ▼
UploadVideoHandler
        ├── validate format + size
        ├── LocalVideoStorage.store()
        ├── VideoJob.withStoragePath().queue()
        ├── DoctrineVideoRepository.save()
        └── MessengerVideoProcessingQueue.dispatch(ProcessVideoMessage)
        │
        ▼
HTTP 201 { videoId, status: "queued" }
```

Verification: [Sprint31-Verification.md](../reports/Sprint31-Verification.md)

---

# Platform Sprint 32 — Speech-to-Text Foundation (2026-06)

Platform Sprint 32 delivers the **speech-to-text foundation** for Phase 2: domain model, Faster-Whisper provider, worker integration, transcript artifact pipeline, frontend viewer, and OpenAPI documentation.

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| P32-SLICE-01 | `Transcript`, `TranscriptSegment`, `TranscriptLanguage`, `SpeechToTextProviderInterface` | ✅ |
| P32-SLICE-02 | `FasterWhisperProvider`, `SpeechToTextProviderFactory`, output parser | ✅ |
| P32-SLICE-03 | `ProcessVideoHandler`, transcript persistence, transcript artifact | ✅ |
| P32-SLICE-04 | `TranscriptPanel`, `TranscriptTimeline`, `TranscriptService` | ✅ |
| P32-SLICE-05 | OpenAPI transcript schemas, architecture docs, verification report | ✅ |

| Layer | Addition |
| ----- | -------- |
| Domain | `Transcript` aggregate, `TranscriptSegment`, `TranscriptLanguage`, STT provider port |
| Application | `ProcessVideoHandler`, `GetVideoTranscriptHandler`, `TranscriptJsonMapper` |
| Infrastructure | `FasterWhisperProvider`, `DoctrineTranscriptRepository`, `FixedFasterWhisperProcessRunner` (test) |
| Presentation | `GetVideoTranscriptController`, OpenAPI `Transcript` / `TranscriptSegment` / `TranscriptLanguage` |
| Frontend | `TranscriptPanel`, `TranscriptTimeline`, `TranscriptService` at `/video/:videoId/transcript` |

```text
Video Upload → ProcessVideoMessage → FasterWhisperProvider
        │
        ▼
Transcript persisted + ArtifactType::Transcript
        │
        ▼
GET /api/videos/{videoId}/transcript → TranscriptPanel
```

Verification: [Sprint32-Verification.md](../reports/Sprint32-Verification.md)

---

# Platform Sprint 33 — Multilingual Translation Foundation (2026-06)

Platform Sprint 33 delivers the **multilingual translation foundation** for Phase 2: domain model, Ollama/Qwen provider, worker integration, translation artifact pipeline, frontend viewer, and OpenAPI documentation.

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| P33-SLICE-01 | `Translation`, `TranslationSegment`, `TranslationLanguage`, `TranslationProviderInterface` | ✅ |
| P33-SLICE-02 | `OllamaTranslationProvider`, `TranslationProviderFactory`, prompt builder | ✅ |
| P33-SLICE-03 | `VideoTranslationGenerator`, translation persistence, translation artifacts, REST API | ✅ |
| P33-SLICE-04 | `TranslationPanel`, `TranslationLanguageTabs`, `TranslationService` | ✅ |
| P33-SLICE-05 | OpenAPI translation schemas, architecture docs, verification report | ✅ |

| Layer | Addition |
| ----- | -------- |
| Domain | `Translation` aggregate, `TranslationSegment`, `TranslationLanguage`, `TranslationProvider`, translation provider port |
| Application | `VideoTranslationGenerator`, `GetVideoTranslationHandler`, `ListVideoTranslationsHandler`, `GenerateVideoTranslationsHandler`, `TranslationJsonMapper` |
| Infrastructure | `OllamaTranslationProvider`, `DoctrineTranslationRepository`, `FixedOllamaClient` (test) |
| Presentation | Translation controllers, OpenAPI `Translation` / `TranslationSegment` / `TranslationLanguage` / `TranslationProvider` |
| Frontend | `TranslationPanel`, `TranslationLanguageTabs`, `TranslationService` at `/video/:videoId/translations` |

```text
Video Upload → Transcript → TranslationProviderFactory → Ollama (Qwen)
        │
        ▼
Translation persisted + ArtifactType::Translation (per language)
        │
        ▼
GET/POST /api/videos/{videoId}/translations → TranslationPanel
```

Verification: [Sprint33-Verification.md](../reports/Sprint33-Verification.md)

---

# Platform Sprint 34 — AI Engine Platform (2026-06)

Platform Sprint 34 delivers the **AI Engine Platform** abstraction layer for Phase 2: unified domain model, provider registry, capability resolution, read-only frontend settings, and OpenAPI documentation. No new user-facing video features — infrastructure only.

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| P34-SLICE-01 | `AIEngine`, `AIEngineCapability`, `AIEngineProvider`, `AIEngineRegistry` | ✅ |
| P34-SLICE-02 | `AIEngineRegistryFactory`, `AIProviderResolver`, provider registration | ✅ |
| P34-SLICE-03 | Capability resolution in `ProcessVideoHandler`, `VideoTranslationGenerator` | ✅ |
| P34-SLICE-04 | `AIEngineSettings`, `AIProviderList`, `AIEngineService` at `/settings/ai` | ✅ |
| P34-SLICE-05 | OpenAPI AI engine schemas, architecture docs, verification report | ✅ |

| Layer | Addition |
| ----- | -------- |
| Domain | `AIEngine` aggregate, `AIEngineCapability`, `AIEngineProvider`, `AIEngineRegistry`, `AIProviderResolverInterface` |
| Application | `ListAIProvidersHandler`; handlers resolve providers by capability |
| Infrastructure | `AIEngineRegistryFactory`, `AIProviderResolver`; registers FasterWhisper, Ollama, future disabled providers |
| Presentation | `GET /api/ai/providers`, OpenAPI `AIEngine` / `AIProvider` / `AIEngineCapability` |
| Frontend | `AIEngineSettings`, `AIProviderList`, `AIEngineService` at `/settings/ai` |

```text
Application Handler
        │
        ▼
AIProviderResolverInterface (capability)
        │
        ▼
AIEngineRegistry → enabled provider
        │
        ├── SpeechToTextProvider → FasterWhisper
        ├── TranslationProvider → Ollama
        ├── TextToSpeechProvider → F5-TTS (Kokoro, XTTS disabled)
        └── (future) VoiceClone, LipSync providers
        │
        ▼
GET /api/ai/providers → AIEngineSettings (/settings/ai)
```

Verification: [Sprint34-Verification.md](../reports/Sprint34-Verification.md)

---

# Platform Sprint 35 — Text-to-Speech Foundation (2026-06)

Platform Sprint 35 delivers **translated audio generation and preview** for Phase 2: TTS domain model, F5-TTS provider integration, audio worker pipeline, frontend audio player, and OpenAPI documentation. No voice cloning, lip-sync, or video rendering.

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| P35-SLICE-01 | `AudioArtifact`, `Voice`, `TextToSpeechProviderInterface` | ✅ |
| P35-SLICE-02 | `F5TextToSpeechProvider`, factory, AI Engine integration | ✅ |
| P35-SLICE-03 | `VideoAudioGenerator`, audio artifacts, REST endpoints | ✅ |
| P35-SLICE-04 | `AudioPlayerPanel`, `VoiceSelector`, `AudioService` | ✅ |
| P35-SLICE-05 | OpenAPI audio schemas, architecture docs, verification report | ✅ |

| Layer | Addition |
| ----- | -------- |
| Domain | `AudioArtifact`, `Voice`, `VoiceCatalog`, `TextToSpeechProvider`, `TextToSpeechProviderInterface` |
| Application | `VideoAudioGenerator`, `GenerateVideoAudioHandler`, `ListVideoAudioHandler`, `GetVideoAudioHandler` |
| Infrastructure | `F5TextToSpeechProvider`, `DoctrineAudioRepository`, `TextToSpeechProviderFactory` |
| Presentation | `GET/POST /api/videos/{videoId}/audio`, stream endpoint, OpenAPI TTS schemas |
| Frontend | `AudioPlayerPanel`, `VoiceSelector`, `AudioService` at `/video/:videoId/audio` |

```text
Translation Artifact
        │
        ▼
AIProviderResolver.resolveTextToSpeech()
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

Verification: [Sprint35-Verification.md](../reports/Sprint35-Verification.md)

---

# Platform Sprint 36 — Voice Cloning Foundation (2026-06)

Platform Sprint 36 delivers **voice cloning** for Phase 2: dedicated `VoiceClone` AI capability (separate from TTS), OpenVoice V2 provider, worker pipeline, frontend compare mode, and OpenAPI documentation. No lip-sync or video rendering.

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| P36-SLICE-01 | `VoiceProfile`, `VoiceCloneArtifact`, `VoiceCloneProviderInterface` | ✅ |
| P36-SLICE-02 | `OpenVoiceProvider`, factory, AI Engine integration | ✅ |
| P36-SLICE-03 | `VideoVoiceCloneGenerator`, voice clone artifacts, REST endpoints | ✅ |
| P36-SLICE-04 | `VoiceClonePanel`, `VoiceModeSelector`, `VoiceCloneService` | ✅ |
| P36-SLICE-05 | OpenAPI voice clone schemas, architecture docs, verification report | ✅ |

| Layer | Addition |
| ----- | -------- |
| Domain | `VoiceProfile`, `VoiceCloneArtifact`, `VoiceCloneProvider`, `VoiceCloneProviderInterface` |
| Application | `VideoVoiceCloneGenerator`, voice clone handlers, `VoiceCloneJsonMapper` |
| Infrastructure | `OpenVoiceProvider`, `DoctrineVoiceCloneRepository`, `VoiceCloneProviderFactory` |
| Presentation | `GET/POST /api/videos/{videoId}/voice-clone`, stream endpoint, OpenAPI schemas |
| Frontend | `VoiceClonePanel`, `VoiceModeSelector`, `VoiceCloneService` at `/video/:videoId/voice-clone` |

```text
Generic Audio (F5-TTS)
        │
        ▼
AIProviderResolver.resolveVoiceClone()
        │
        ▼
OpenVoiceProvider
        │
        ▼
VoiceCloneArtifact (ArtifactType::VoiceClone)
        │
        ▼
GET/POST /api/videos/{videoId}/voice-clone → VoiceClonePanel (/video/:videoId/voice-clone)
```

Verification: [Sprint36-Verification.md](../reports/Sprint36-Verification.md)

---

# Platform Sprint 37 — Lip Sync Foundation (2026-06)

Platform Sprint 37 delivers **lip sync** for Phase 2: dedicated `LipSync` AI capability, LatentSync provider, worker pipeline, frontend video preview with before/after comparison, and OpenAPI documentation. No final MP4 rendering or export.

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| P37-SLICE-01 | `LipSyncArtifact`, `LipSyncProviderInterface` | ✅ |
| P37-SLICE-02 | `LatentSyncProvider`, factory, AI Engine integration | ✅ |
| P37-SLICE-03 | `VideoLipSyncGenerator`, lip sync artifacts, REST endpoints | ✅ |
| P37-SLICE-04 | `LipSyncPreview`, `LipSyncSettings`, `LipSyncService` | ✅ |
| P37-SLICE-05 | OpenAPI lip sync schemas, architecture docs, verification report | ✅ |

| Layer | Addition |
| ----- | -------- |
| Domain | `LipSyncArtifact`, `LipSyncVideo`, `LipSyncProvider`, `LipSyncProviderInterface` |
| Application | `VideoLipSyncGenerator`, lip sync handlers, `LipSyncJsonMapper` |
| Infrastructure | `LatentSyncProvider`, `DoctrineLipSyncRepository`, `LipSyncProviderFactory` |
| Presentation | `GET/POST /api/videos/{videoId}/lip-sync`, stream endpoint, OpenAPI schemas |
| Frontend | `LipSyncPanel`, `LipSyncPreview`, `LipSyncSettings` at `/video/:videoId/lip-sync` |

```text
Original Video + Cloned Audio
        │
        ▼
AIProviderResolver.resolveLipSync()
        │
        ▼
LatentSyncProvider
        │
        ▼
LipSyncArtifact (ArtifactType::LipSync)
        │
        ▼
GET/POST /api/videos/{videoId}/lip-sync → LipSyncPanel (/video/:videoId/lip-sync)
```

Verification: [Sprint37-Verification.md](../reports/Sprint37-Verification.md)

---

# Platform Sprint 38 — Final Video Rendering (2026-06)

Platform Sprint 38 delivers **final MP4 rendering and download** for Phase 2: dedicated `VideoRender` AI capability, FFmpeg provider, worker pipeline, frontend final video player with download, and OpenAPI documentation. This completes the first end-to-end demonstrable localization pipeline.

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| P38-SLICE-01 | `FinalVideoArtifact`, `VideoRenderProviderInterface` | ✅ |
| P38-SLICE-02 | `FFmpegVideoRenderProvider`, factory, AI Engine integration | ✅ |
| P38-SLICE-03 | `VideoFinalRenderGenerator`, final video artifacts, REST endpoints | ✅ |
| P38-SLICE-04 | `FinalVideoPanel`, `FinalVideoPlayer`, `RenderSettings` at `/video/:videoId/render` | ✅ |
| P38-SLICE-05 | OpenAPI render schemas, architecture docs, verification report | ✅ |

| Layer | Components |
| ----- | ---------- |
| Domain | `FinalVideoArtifact`, `VideoRenderProvider`, `VideoRenderFormat`, `VideoRenderQuality` |
| Application | `VideoFinalRenderGenerator`, render handlers, `VideoRenderJsonMapper` |
| Infrastructure | `FFmpegVideoRenderProvider`, `DoctrineFinalVideoRepository` |
| Presentation | `GET/POST /api/videos/{videoId}/render`, stream endpoint, OpenAPI schemas |
| Frontend | `FinalVideoPanel`, `FinalVideoPlayer`, `RenderSettings` at `/video/:videoId/render` |

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

Verification: [Sprint38-Verification.md](../reports/Sprint38-Verification.md)

---

# Platform Sprint 39 — AI Engine Selector & Pipeline Configuration (2026-06)

Platform Sprint 39 delivers **user-configurable AI pipeline selection** for Phase 2: domain model for pipeline stages, persistence, runtime provider resolution via `AIProviderResolver`, frontend pipeline builder, and OpenAPI documentation. Users choose the AI engine for each processing step before running localization.

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| P39-SLICE-01 | `PipelineConfiguration`, `PipelineStage`, `PipelineStageType` domain | ✅ |
| P39-SLICE-02 | Doctrine persistence, save/load/reset handlers, REST endpoints | ✅ |
| P39-SLICE-03 | `AIProviderResolver` reads pipeline config with registry fallback | ✅ |
| P39-SLICE-04 | `PipelineBuilder`, `PipelineStageSelector` at `/settings/pipeline` | ✅ |
| P39-SLICE-05 | OpenAPI pipeline schemas, architecture docs, verification report | ✅ |

| Layer | Components |
| ----- | ---------- |
| Domain | `PipelineConfiguration`, `PipelineStage`, `PipelineStageType`, repository port |
| Application | `SavePipelineConfigurationHandler`, `LoadPipelineConfigurationHandler`, `ResetPipelineConfigurationHandler` |
| Infrastructure | `DoctrinePipelineConfigurationRepository`, `AIProviderResolver` integration |
| Presentation | `GET/PUT /api/pipeline`, `POST /api/pipeline/reset`, OpenAPI schemas |
| Frontend | `PipelineBuilder`, `PipelineStageSelector`, `PipelineService` at `/settings/pipeline` |

```text
PipelineBuilder (/settings/pipeline)
        │
        ▼
PUT /api/pipeline → PipelineConfiguration (persisted)
        │
        ▼
ProcessVideoHandler → AIProviderResolver
        ├── resolveSpeechToText()     ← configured provider
        ├── resolveTranslation()
        ├── resolveTextToSpeech()
        ├── resolveVoiceClone()
        ├── resolveLipSync()
        └── resolveVideoRender()
        │
        ▼
Final MP4
```

Verification: [Sprint39-Verification.md](../reports/Sprint39-Verification.md)

---

# Platform Sprint 40 — AI Orchestrator Foundation (2026-06)

Platform Sprint 40 delivers **automatic AI pipeline orchestration** for Phase 2: orchestrator domain, deterministic planner, runtime integration with ephemeral pipeline configuration, frontend processing mode selector, and OpenAPI documentation. Users choose manual or automatic mode on video upload.

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| P40-SLICE-01 | `ProcessingMode`, `ProcessingStrategy`, `PipelineRecommendation`, `VideoAnalysis` domain | ✅ |
| P40-SLICE-02 | `DeterministicPipelinePlanner` with registry-based provider selection | ✅ |
| P40-SLICE-03 | Runtime pipeline context, `ProcessVideoHandler` automatic mode integration | ✅ |
| P40-SLICE-04 | `ProcessingModeSelector`, `PipelineRecommendationPanel` on `/video/upload` | ✅ |
| P40-SLICE-05 | OpenAPI orchestrator schemas, architecture docs, verification report | ✅ |

```text
Upload Video (/video/upload)
        │
        ├── Manual → saved PipelineConfiguration (Sprint 39)
        │
        └── Automatic → AI Orchestrator → PipelineRecommendation
                │
                ▼
        RuntimePipelineConfigurationContext (ephemeral)
                │
                ▼
        AIProviderResolver → Pipeline Engine → Final MP4
```

Verification: [Sprint40-Verification.md](../reports/Sprint40-Verification.md)

---

# Platform Sprint 41 — AI Director: Smart Video Intelligence (2026-06)

Platform Sprint 41 delivers **smart video intelligence** for Phase 2: composite deterministic analyzer (audio, visual, speech), AI Director integration with the orchestrator, structured recommendation reasons, frontend intelligence dashboard on upload, and OpenAPI documentation.

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| P41-SLICE-01 | `VideoIntelligence`, characteristics aggregates, enums | ✅ |
| P41-SLICE-02 | `AudioAnalyzer`, `VisualAnalyzer`, `SpeechAnalyzer`, `CompositeVideoAnalyzer` | ✅ |
| P41-SLICE-03 | Orchestrator uses `VideoIntelligence`, explanation reasons, `GET /api/videos/{videoId}/intelligence` | ✅ |
| P41-SLICE-04 | `VideoIntelligenceDashboard`, `RecommendationReasons`, `QualityIndicators` on `/video/upload` | ✅ |
| P41-SLICE-05 | OpenAPI intelligence schemas, architecture docs, verification report | ✅ |

```text
Upload Video (/video/upload)
        │
        ▼
CompositeVideoAnalyzer → VideoIntelligence
        │
        ▼
DeterministicPipelinePlanner → PipelineRecommendation (with reasons)
        │
        ▼
VideoIntelligenceDashboard + Pipeline Engine → Final MP4
```

Verification: [Sprint41-Verification.md](../reports/Sprint41-Verification.md)

---

# Platform Sprint 42 — Adaptive Prompt & Model Optimization (2026-06)

Platform Sprint 42 delivers **execution optimization** for Phase 2: a pure optimization domain, deterministic optimizer driven by `VideoIntelligence`, pipeline integration for manual and automatic modes, frontend optimization dashboard on upload, and OpenAPI documentation.

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| P42-SLICE-01 | `ExecutionOptimization`, `OptimizationStage`, `OptimizationParameter` aggregates | ✅ |
| P42-SLICE-02 | `DeterministicExecutionOptimizer` (beam size, chunk size, style, stability, strength, preset) | ✅ |
| P42-SLICE-03 | Pipeline integration, `GET /api/videos/{videoId}/optimization` | ✅ |
| P42-SLICE-04 | `OptimizationDashboard`, `OptimizationParameterList`, `OptimizationQualitySummary` on `/video/upload` | ✅ |
| P42-SLICE-05 | OpenAPI optimization schemas, architecture docs, verification report | ✅ |

```text
Upload Video (/video/upload)
        │
        ▼
VideoIntelligence
        │
        ▼
DeterministicExecutionOptimizer → ExecutionOptimization
        │
        ▼
OptimizationDashboard + Pipeline Engine → Final MP4
```

Verification: [Sprint42-Verification.md](../reports/Sprint42-Verification.md)

---

# Platform Sprint 43 — Parallel GPU/CPU Orchestration (2026-06)

Platform Sprint 43 delivers **resource-aware pipeline scheduling** for Phase 2: scheduling domain with CPU/GPU/IO requirements, deterministic queue scheduler, runtime progress tracking in `ProcessVideoHandler`, frontend processing monitor, and OpenAPI documentation.

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| P43-SLICE-01 | `ExecutionSchedule`, `ScheduledStage`, `ExecutionResource` aggregates | ✅ |
| P43-SLICE-02 | `DeterministicPipelineScheduler` with strategy-aware concurrency | ✅ |
| P43-SLICE-03 | Pipeline integration, `GET /api/videos/{videoId}/schedule` | ✅ |
| P43-SLICE-04 | `ProcessingResourceMonitor`, `StageProgressTimeline`, `ResourceQueueBadge` | ✅ |
| P43-SLICE-05 | OpenAPI schedule schemas, architecture docs, verification report | ✅ |

```text
Upload Video (/video/upload)
        │
        ▼
Execution Optimization
        │
        ▼
DeterministicPipelineScheduler → ExecutionSchedule
        │
        ▼
ProcessingResourceMonitor + Pipeline Engine → Final MP4
```

Verification: [Sprint43-Verification.md](../reports/Sprint43-Verification.md)

---

# Platform Sprint 44 — Automatic Quality Assessment (2026-06)

Platform Sprint 44 delivers **deterministic AI quality assessment** for Phase 3: quality domain with per-category scores and publication recommendations, deterministic evaluator driven by `VideoIntelligence` and render artifacts, pipeline integration after final render, frontend quality dashboard on upload, and OpenAPI documentation.

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| P44-SLICE-01 | `QualityReport`, `QualityScore`, `QualityMetric`, `PublicationRecommendation` | ✅ |
| P44-SLICE-02 | `DeterministicQualityEvaluator` with explainable rules | ✅ |
| P44-SLICE-03 | Pipeline integration, `GET /api/videos/{videoId}/quality` | ✅ |
| P44-SLICE-04 | `QualityDashboard`, `QualityScoreCard`, `QualityRecommendation` | ✅ |
| P44-SLICE-05 | OpenAPI quality schemas, architecture docs, verification report | ✅ |

```text
Final MP4
        │
        ▼
DeterministicQualityEvaluator → QualityReport artifact
        │
        ▼
QualityDashboard + GET /api/videos/{videoId}/quality
```

Verification: [Sprint44-Verification.md](../reports/Sprint44-Verification.md)

---

# Platform Sprint 45 — Project Workspace & Batch Processing (2026-06)

Platform Sprint 45 transforms the Core AI Platform into a **production workspace**: projects organize multiple videos, batch processing reuses the existing pipeline per video with isolated failures, runtime integration updates aggregate progress, and the frontend workspace exposes project management at `/workspace`.

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| P45-SLICE-01 | `Project`, `BatchJob`, collections, domain validation | ✅ |
| P45-SLICE-02 | `RunBatchProcessingHandler` with pipeline reuse | ✅ |
| P45-SLICE-03 | Doctrine persistence, REST API, worker batch progress | ✅ |
| P45-SLICE-04 | `WorkspacePage`, `ProjectCard`, `VideoGrid`, `BatchProgress` | ✅ |
| P45-SLICE-05 | OpenAPI project schemas, architecture docs, verification report | ✅ |

```text
Workspace → Project → Batch Processing → AI Director → Pipeline → Final Videos
```

Verification: [Sprint45-Verification.md](../reports/Sprint45-Verification.md)

---

# Platform Sprint 46 — Execution History, Versioning & Reprocessing (2026-06)

Platform Sprint 46 adds **reproducible production workflows**: every completed render is historized with pipeline, optimization, and quality snapshots; versions are comparable; and any previous version can be reprocessed with optional provider overrides.

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| P46-SLICE-01 | `ExecutionHistory`, `ExecutionVersion`, `ExecutionSnapshot` domain | ✅ |
| P46-SLICE-02 | `RecordExecutionHistoryHandler`, compare engine, persistence | ✅ |
| P46-SLICE-03 | Reprocessing integration, REST API, worker history recording | ✅ |
| P46-SLICE-04 | `ExecutionHistoryPanel`, `VersionTimeline`, `ExecutionComparison` | ✅ |
| P46-SLICE-05 | OpenAPI history schemas, architecture docs, verification report | ✅ |

```text
Workspace → Execution History → Compare Versions → Reprocess → Final MP4
```

Verification: [Sprint46-Verification.md](../reports/Sprint46-Verification.md)

---

# Platform Sprint 47 — AI Review & Human Feedback Loop (2026-06)

Platform Sprint 47 adds **user-centered adaptivity**: creators rate generated outputs, the platform derives a deterministic preference profile, and the AI Director adjusts pipeline recommendations accordingly.

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| P47-SLICE-01 | `Review`, `ReviewScore`, `UserPreferenceProfile` domain | ✅ |
| P47-SLICE-02 | Feedback engine with profile generation and persistence | ✅ |
| P47-SLICE-03 | AI Director preference integration | ✅ |
| P47-SLICE-04 | `ReviewPanel`, `ReviewSummary`, `PreferenceProfileCard` | ✅ |
| P47-SLICE-05 | OpenAPI review schemas, architecture docs, verification report | ✅ |

```text
Execution History → User Reviews → Preference Profile → AI Director → Optimized Pipeline
```

Verification: [Sprint47-Verification.md](../reports/Sprint47-Verification.md)

---

# Platform Sprint 48 — Team Collaboration & Shared Workspaces (2026-06)

Platform Sprint 48 transforms History AI from a personal tool into a **collaborative SaaS platform**: teams share workspaces, assign roles, send deterministic invitations, and enforce permissions across processing, review, and project management.

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| P48-SLICE-01 | Collaboration domain model | ✅ |
| P48-SLICE-02 | Membership engine and persistence | ✅ |
| P48-SLICE-03 | Authorization integration across handlers | ✅ |
| P48-SLICE-04 | `TeamPanel`, `CollaborationService` | ✅ |
| P48-SLICE-05 | OpenAPI collaboration schemas, docs, verification report | ✅ |

```text
Organization → Workspace → Team → Projects → AI Director → Pipeline → Final Videos
```

Verification: [Sprint48-Verification.md](../reports/Sprint48-Verification.md)

---

# Platform Sprint 49 — Observability, Monitoring & Analytics (2026-06)

Platform Sprint 49 makes History AI **operable in production** by capturing pipeline telemetry, aggregating workspace analytics, and exposing an analytics dashboard for administrators.

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| P49-SLICE-01 | Telemetry domain model | ✅ |
| P49-SLICE-02 | Metrics collection engine and persistence | ✅ |
| P49-SLICE-03 | Runtime pipeline instrumentation | ✅ |
| P49-SLICE-04 | Analytics dashboard and `TelemetryService` | ✅ |
| P49-SLICE-05 | OpenAPI telemetry schemas, docs, verification report | ✅ |

```text
Workspace → Projects → Pipeline → Telemetry → Analytics Dashboard
```

Verification: [Sprint49-Verification.md](../reports/Sprint49-Verification.md)

---

# Platform Sprint 50.5 — Product Information Architecture (2026-07)

Platform Sprint 50.5 aligns the visible product structure with **Knowledge In → AI Processing → Knowledge / Media Out** without merging backend Content and Video domains.

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| P50.5-SLICE-01 | WorkItem product read model (`frontend/src/services/workItem/`) | ✅ |
| P50.5-SLICE-02 | Home Mission Control (replaces legacy dashboard at `/`) | ✅ |
| P50.5-SLICE-03 | Actionable recent work and stats routing | ✅ |
| P50.5-SLICE-04 | Video Overview hub at `/video/:videoId` | ✅ |
| P50.5-SLICE-05 | Sidebar icons, product language, empty states | ✅ |
| P50.5-SLICE-06 | Architecture docs and verification | ✅ |

```text
Home → WorkItem → Video Overview → Feature pages
Workspace = projects + batch + team + analytics (unchanged role)
```

See [PRODUCT_INFORMATION_ARCHITECTURE.md](./PRODUCT_INFORMATION_ARCHITECTURE.md).

Verification: [Sprint50_5-Verification.md](../reports/Sprint50_5-Verification.md)

---

# Platform Sprint 51 — Source Processing Platform (Audio) (2026-07)

Platform Sprint 51 introduces unified **source ingestion** with the first connector: **audio upload** (mp3, wav, flac, m4a, ogg). Pipeline reuses STT and translation; no video-only stages.

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| P51-SLICE-01 | Source domain | ✅ |
| P51-SLICE-02 | Audio upload API | ✅ |
| P51-SLICE-03 | Audio processing pipeline | ✅ |
| P51-SLICE-04 | Frontend `/audio/upload`, `/audio/:id` | ✅ |
| P51-SLICE-05 | Docs and verification | ✅ |

See [SOURCE_PROCESSING_PLATFORM.md](./SOURCE_PROCESSING_PLATFORM.md).

Verification: [Sprint51-Verification.md](../reports/Sprint51-Verification.md)

---

# Platform Sprint 52 — YouTube Processing Platform (2026-07)

Platform Sprint 52 adds **YouTube URL import** as a Source connector. Downloads via `yt-dlp`, creates a `VideoJob`, and runs the **existing video pipeline** — no YouTube-specific processing stages.

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| P52-SLICE-01 | YouTube domain + `SourceType::Youtube` | ✅ |
| P52-SLICE-02 | Import API (`POST/GET /api/youtube`) | ✅ |
| P52-SLICE-03 | Video pipeline integration | ✅ |
| P52-SLICE-04 | Frontend `/youtube/import`, WorkItem | ✅ |
| P52-SLICE-05 | Docs and verification | ✅ |

See [SOURCE_PROCESSING_PLATFORM.md](./SOURCE_PROCESSING_PLATFORM.md).

Verification: [Sprint52-Verification.md](../reports/Sprint52-Verification.md)

---

# Platform Sprint 53 — Internationalization & Localization (2026-07)

Platform Sprint 53 makes the **frontend UI multilingual** (English, French, German) before the Public API. User-generated content and provider names are not translated.

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| P53-SLICE-01 | i18n foundation (`useTranslation`, persistence) | ✅ |
| P53-SLICE-02 | Product shell + language selector | ✅ |
| P53-SLICE-03 | Pipeline features (Sprints 31–44) | ✅ |
| P53-SLICE-04 | Workspace, review, analytics | ✅ |
| P53-SLICE-05 | Audit script + documentation | ✅ |

Location: `frontend/src/i18n/`

Verification: [Sprint53-Verification.md](../reports/Sprint53-Verification.md) · [Sprint53-LocalizationAudit.md](../reports/Sprint53-LocalizationAudit.md)

---

# Sprint 54 — Lumen Rebrand & Compact Product Layout (2026-06)

| Slice | Focus | Status |
|-------|-------|--------|
| P54-SLICE-01 | User-visible rebrand to **Lumen** | ✅ |
| P54-SLICE-02 | Compact create layouts (`CreatePageLayout`, collapsed help) | ✅ |
| P54-SLICE-03 | Workspace local tabs + sticky batch bar | ✅ |
| P54-SLICE-04 | Guided empty states + sidebar disabled hints | ✅ |
| P54-SLICE-05 | Documentation + verification | ✅ |

- **Brand:** Lumen in UI; backend namespaces / DB names unchanged.
- **UX:** Create pages use two-column layout; workspace uses lazy-loaded tabs.
- **No backend changes** in this sprint.

Verification: [Sprint54-Verification.md](../reports/Sprint54-Verification.md)

Product IA updates: [PRODUCT_INFORMATION_ARCHITECTURE.md](./PRODUCT_INFORMATION_ARCHITECTURE.md)

---

# Sprint 55 — Shadow AI Watch Companion (2026-06)

| Slice | Focus | Status |
|-------|-------|--------|
| P55-SLICE-01 | Shadow session domain aggregate | ✅ |
| P55-SLICE-02 | Timeline context engine + `GET .../shadow/context` | ✅ |
| P55-SLICE-03 | Contextual Q&A + pause/resume commands | ✅ |
| P55-SLICE-04 | Frontend `/video/:videoId/watch` + voice MVP | ✅ |
| P55-SLICE-05 | OpenAPI, architecture docs, verification | ✅ |

- **Bounded context:** `Domain/Shadow` — session, interactions, playback state.
- **Reuses** transcript, translation, and chat provider; does **not** duplicate video processing.
- **Playback:** browser controls video; backend models session state only.

Architecture: [SHADOW_WATCH_COMPANION.md](./SHADOW_WATCH_COMPANION.md)

Verification: [Sprint55-Verification.md](../reports/Sprint55-Verification.md)

---

# Sprint 56 — Shadow Proactive Tutor (2026-06)

| Slice | Focus | Status |
|-------|-------|--------|
| P56-SLICE-01 | Proactive intervention domain | ✅ |
| P56-SLICE-02 | Deterministic intervention engine | ✅ |
| P56-SLICE-03 | Intervention API + session policy | ✅ |
| P56-SLICE-04 | Frontend tutor settings + intervention UX | ✅ |
| P56-SLICE-05 | OpenAPI, architecture docs, verification | ✅ |

- **Optional proactive tutor** on `/video/:videoId/watch`; disabled by default.
- **Backend recommends** pause/resume; browser controls playback.
- **Extends** Sprint 55 Shadow bounded context; no pipeline duplication.

Architecture: [SHADOW_PROACTIVE_TUTOR.md](./SHADOW_PROACTIVE_TUTOR.md)

Verification: [Sprint56-Verification.md](../reports/Sprint56-Verification.md)

---

# Sprint 56.5 — Shadow Multilingual Voice (2026-06)

| Slice | Focus | Status |
|-------|-------|--------|
| P56.5-SLICE-01 | Voice language domain | ✅ |
| P56.5-SLICE-02 | Answer language resolution + API metadata | ✅ |
| P56.5-SLICE-03 | Frontend voice settings + browser TTS/STT | ✅ |
| P56.5-SLICE-04 | Multilingual voice tests | ✅ |
| P56.5-SLICE-05 | OpenAPI, docs, verification | ✅ |

- **UI i18n ≠ voice i18n** — buttons are translated; Shadow speech is a separate layer.
- Browser `speechSynthesis` / `SpeechRecognition` with text fallback.
- Server neural TTS deferred to a future sprint.

Verification: [Sprint56_5-Verification.md](../reports/Sprint56_5-Verification.md)

---

# Project architecture overview

**Lumen** is a **modular monolith** with three runtime applications and a shared domain story:

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
