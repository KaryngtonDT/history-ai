# Platform Sprint 25 — Final Verification Report

Version: 1.0

Status: Accepted

Date: 2026-06-26

---

# Executive summary

Platform Sprint 25 delivers **multi-document RAG conversations**: domain model, Doctrine persistence, RAG across selected documents, document selection API, frontend `DocumentSelector`, and OpenAPI documentation. Slice 5 changed **OpenAPI documentation, architecture docs, and verification only** — no business logic in backend handlers, frontend, or worker.

| Area | Result |
| ---- | ------ |
| Backend PHPUnit | ✅ 792 tests, 2675 assertions |
| Backend architecture | ✅ 36 tests, 45 assertions |
| Backend OpenAPI | ✅ 47 tests, 520 assertions |
| Frontend build | ✅ OK |
| Frontend Vitest | ✅ 462 tests (93 files) |
| Frontend Biome | ✅ clean (446 files) |
| Worker pytest | ✅ 127 tests |
| Worker Ruff | ✅ All checks passed |
| Document selection API | ✅ Documented; behavior unchanged |
| Multi-document RAG | ✅ Documented; behavior unchanged |

---

# Platform Sprint 25 scope (slices 01–05)

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| P25-SLICE-01 | `SelectedDocument`, `SelectedDocumentCollection`; multi-doc `Conversation` domain | ✅ |
| P25-SLICE-02 | `documents` JSON column; `DoctrineConversationRepository` multi-doc | ✅ |
| P25-SLICE-03 | `ContentChatAnswerer`; RAG across all selected documents | ✅ |
| P25-SLICE-04A | `PUT /api/conversations/{conversationId}/documents` | ✅ |
| P25-SLICE-04B | Frontend `DocumentSelector`; `ConversationService.updateDocuments()` | ✅ |
| P25-SLICE-05 | OpenAPI multi-doc schemas, architecture docs, this report | ✅ |

---

# Final architecture

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
Conversation.documents[]
    │
    ▼
POST /api/contents/{contentId}/conversations/{conversationId}/chat
    │
    ▼
AskConversationChatHandler
    │
    ├── load artifacts per SelectedDocumentCollection (ordered)
    └── ContentChatAnswerer.answer(merged artifacts, question)
    │
    ▼
ConversationChatResponse JSON
    │
    ▼
Frontend renders conversation.messages + documents[]
```

Legacy single-turn endpoints remain available:

| Endpoint | Role |
| -------- | ---- |
| `POST /api/contents/{contentId}/chat` | Single-turn synchronous answer |
| `POST /api/contents/{contentId}/chat/stream` | Single-turn SSE streaming |

`ChatPanel` uses the conversation endpoint for questions and the documents endpoint for selection. `chatService.streamQuestion()` is preserved for future conversation-aware streaming.

---

# P25-SLICE-01 — Multi-Document Domain

| Component | Role |
| --------- | ---- |
| `SelectedDocument` | Value object wrapping `ContentId` |
| `SelectedDocumentCollection` | Ordered, deduplicated document list; minimum one document |
| `Conversation` | Aggregate with `documents()`; `withDocuments()`; `contentId()` = primary (first) document |
| Exceptions | `InvalidConversationDocumentException`, `ConversationNotFoundException` |

Domain-only; no infrastructure dependencies.

---

# P25-SLICE-02 — Multi-Document Repository

| Component | Role |
| --------- | ---- |
| `ConversationRecord.documents` | JSON column storing `[{contentId}]` |
| `DoctrineConversationRepository` | Persists and loads `documents[]`; `findByContentId` matches any selected document |
| Migration | `Version20260630100000` |

Integration tests verify multi-doc create, update, list-by-content, and round-trip.

---

# P25-SLICE-03 — Multi-Document Chat API

| Component | Role |
| --------- | ---- |
| `ContentChatAnswerer` | Shared RAG pipeline for single- and multi-document handlers |
| `AskConversationChatHandler` | Loads artifacts from all `conversation.documents()` in order; delegates to `ContentChatAnswerer` |
| `containsDocument()` | Route `contentId` must be in selected documents |

RAG retrieval merges artifacts from all selected documents. `AskContentChatHandler` behavior unchanged for single-document chat.

---

# P25-SLICE-04A — Document Selection API

| Component | Role |
| --------- | ---- |
| `UpdateConversationDocumentsCommand` | `conversationId`, `contentIds[]` |
| `UpdateConversationDocumentsHandler` | Replace selection; preserve messages; no RAG |
| `UpdateConversationDocumentsController` | `PUT /api/conversations/{conversationId}/documents` |
| `ConversationResponse` | `{ conversation: { id, contentId, messages[], documents[] } }` |

**Request:** `UpdateConversationDocumentsRequest` (`contentIds[]`, minimum one UUID).

**Errors:** HTTP 400 `ErrorResponse` for invalid UUID, malformed JSON, empty list, or invalid content id; HTTP 404 `ErrorResponse` for unknown conversation.

---

# P25-SLICE-04B — Frontend Document Selector

| Component | Role |
| --------- | ---- |
| `DocumentSelector` | Props-only checkboxes; `onSelectionChange(contentIds)` |
| `ConversationService.updateDocuments()` | `PUT /api/conversations/{conversationId}/documents` |
| `HttpConversationRepository` | `HttpClient.put` with `conversationDocumentsPath` |
| `ChatPanel` | Shows selector after first conversation; backend response is source of truth |

Changing document selection does not trigger a chat question. At least one document remains selected in the UI.

---

# P25-SLICE-05 — OpenAPI & Documentation

| Item | Location |
| ---- | -------- |
| `SelectedDocument` schema | `Presentation/OpenApi/Schema/SelectedDocument.php` |
| `UpdateConversationDocumentsRequest` schema | `Presentation/OpenApi/Schema/UpdateConversationDocumentsRequest.php` |
| `ConversationResponse` schema | `Presentation/OpenApi/Schema/ConversationResponse.php` |
| `Conversation` schema update | `documents[]` added |
| Controller annotations | `UpdateConversationDocumentsController` — `#[OA\Put]`, tag `Chat` |
| Nelmio aliases | `nelmio_api_doc.yaml` |
| Architecture docs | `docs/architecture/README.md`, `architecture-rules.md`, `openapi.md` |

OpenAPI tests verify path existence, `UpdateConversationDocumentsRequest` body, 200 `ConversationResponse`, 400/404 `ErrorResponse`, `SelectedDocument` schema, and `Conversation.documents[]`.

---

# Validation commands

```bash
docker compose exec backend php bin/phpunit
docker compose exec backend php bin/phpunit tests/Architecture
docker compose exec backend php bin/phpunit tests/Functional/OpenApi
docker compose exec frontend npm run build
docker compose exec frontend npm test
docker compose exec frontend npm run check
docker compose exec worker pytest
docker compose exec worker ruff check .
```

All suites passed on 2026-06-26 after backend container sync.

---

# Known limitations

| Topic | Current state |
| ----- | ------------- |
| Document list endpoint | No dedicated API to list available documents for a conversation |
| Selector data source | `DocumentSelector` uses `contentId` + `artifacts[].contentId` already loaded in the UI |
| Conversation streaming | `POST /chat/stream` remains single-content; no multi-doc conversation stream |
| Document weighting | All selected documents contribute equally to RAG retrieval |
| Citation grouping | Citations are not grouped or labeled by source document in the UI |
| Vector index | No persistent per-document vector index beyond existing artifact chunks |
| Conversation list | No `GET` endpoint to list conversations for a content |
| Provider context | RAG uses current question only; full thread context in provider is future work |

---

# Future work

| Item | Rationale |
| ---- | --------- |
| Conversation streaming multi-doc | `POST …/conversations/{id}/chat/stream` with persistence |
| Document search/list API | Populate selector from backend without relying on loaded artifacts |
| Document weighting | Prioritize primary document or user-defined weights in RAG |
| Multi-doc citations grouped by document | Clearer source attribution in answers |
| Persistent vector index | Faster cross-document retrieval at scale |
| Conversation list | `GET /contents/{contentId}/conversations` for history sidebar |
| Provider thread context | Pass prior messages to `ChatProvider` for true multi-turn RAG |

Suggested roadmap:

- **Sprint 26** — Agentic Workflows
- **Sprint 27** — Production Deployment

---

# Documentation tree

```text
docs/
├── architecture/
│   ├── README.md              (Platform Sprint 25 section added)
│   ├── architecture-rules.md  (multi-document conversation layer)
│   └── openapi.md             (PUT …/conversations/{conversationId}/documents)
└── reports/
    ├── Sprint20-Verification.md
    ├── Sprint21-Verification.md
    ├── Sprint22-Verification.md
    ├── UX01-Verification.md
    ├── UX02-Verification.md
    ├── UX03-Verification.md
    ├── Platform23-Verification.md
    ├── Sprint24-Verification.md
    └── Sprint25-Verification.md
```

---

# Platform capabilities after Sprint 25

| Capability | Status |
| ---------- | ------ |
| Semantic Search | ✅ |
| Vector Store | ✅ |
| Embedding Providers | ✅ |
| Chat (single-turn) | ✅ |
| Streaming (single-turn) | ✅ |
| Interactive Citations | ✅ |
| Performance Metrics | ✅ |
| Embedding Cache | ✅ |
| Persistent Conversations | ✅ |
| Multi-Document Conversations | ✅ |
| Multi-Document RAG | ✅ |
| Frontend Document Selector | ✅ |
