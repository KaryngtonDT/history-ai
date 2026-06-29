# Platform Sprint 26 — Final Verification Report

Version: 1.0

Status: Accepted

Date: 2026-06-26

---

# Executive summary

Platform Sprint 26 delivers **conversation-aware streaming chat** end to end: domain stream model, SSE API with post-stream persistence, frontend `ChatPanel` integration, and OpenAPI documentation. Slice 4 changed **OpenAPI documentation, architecture docs, and verification only** — no business logic in backend handlers, frontend, or worker.

| Area | Result |
| ---- | ------ |
| Backend PHPUnit | ✅ 825 tests, 2795 assertions |
| Backend architecture | ✅ 36 tests, 45 assertions |
| Backend OpenAPI | ✅ 51 tests, 551 assertions |
| Frontend build | ✅ OK |
| Frontend Vitest | ✅ 472 tests (93 files) |
| Frontend Biome | ✅ clean (446 files) |
| Worker pytest | ✅ 127 tests |
| Worker Ruff | ✅ All checks passed |
| Conversation stream API | ✅ Documented; behavior unchanged |
| Frontend streaming UX | ✅ Documented; behavior unchanged |

---

# Platform Sprint 26 scope (slices 01–04)

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| P26-SLICE-01 | `ConversationStream`, `ConversationStreamEvent` domain | ✅ |
| P26-SLICE-02 | `POST …/conversations/{conversationId}/chat/stream` SSE + persistence | ✅ |
| P26-SLICE-03 | `ConversationService.streamQuestion()`; `ChatPanel` streaming UX | ✅ |
| P26-SLICE-04 | OpenAPI `ConversationStreamEvent`, architecture docs, this report | ✅ |

---

# Final architecture

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
    ├── resolve / create conversation
    ├── ContentChatStreamer (multi-doc RAG)
    ├── ConversationStream from ChatStream tokens
    └── ConversationRepository.save (after full stream)
    │
    ▼
SSE: token → token → … → conversation → done
    │
    ▼
Frontend replaces local state with conversation event payload
```

Legacy endpoints remain available:

| Endpoint | Role |
| -------- | ---- |
| `POST /api/contents/{contentId}/chat` | Single-turn synchronous answer with citations |
| `POST /api/contents/{contentId}/chat/stream` | Single-turn SSE (`token` + `done`) |
| `POST /api/contents/{contentId}/conversations/{conversationId}/chat` | Multi-doc synchronous conversation chat |

`ChatPanel` uses the conversation **streaming** endpoint for questions and `PUT …/documents` for document selection.

---

# P26-SLICE-01 — ConversationStream Domain

| Component | Role |
| --------- | ---- |
| `ConversationStream` | Aggregate of stream events for one conversation |
| `ConversationStreamEvent` | Indexed token (`index`, `text`) |
| `ConversationStreamEventCollection` | Ordered, validated event list |
| `toAssistantMessage()` | Builds assistant `ChatMessage` after stream completes |

Domain-only; separate from `ChatStream` used by single-turn streaming.

---

# P26-SLICE-02 — Conversation Streaming API

| Component | Role |
| --------- | ---- |
| `AskConversationChatStreamHandler` | Mirrors `AskConversationChatHandler`; persists after stream |
| `ContentChatStreamer` | Shared multi-doc RAG + `StreamingChatProviderInterface` |
| `ConversationChatStreamResponse` | SSE: `token`, `conversation`, `done` |

**Request:** `ChatRequest` (`question`, 1–2000 characters).

**SSE events:**

| Event | Payload |
| ----- | ------- |
| `token` | `ChatStreamToken` (`index`, `text`) |
| `conversation` | `ConversationStreamEvent` (`conversation` with `id`, `contentId`, `messages[]`, `documents[]`) |
| `done` | `{}` |

**Errors:** HTTP 400 `ErrorResponse` for invalid UUID, malformed JSON, invalid question, or conversation/content mismatch.

---

# P26-SLICE-03 — Frontend Conversation Streaming

| Component | Role |
| --------- | ---- |
| `ConversationService.streamQuestion()` | Validates IDs/question; delegates to repository |
| `HttpConversationRepository` | `fetch()` for SSE; parses `token`, `conversation`, `done` |
| `MockConversationRepository` | Deterministic tokens + conversation + done |
| `ChatPanel` | Optimistic user message; growing assistant bubble; `conversation` event = source of truth |

`DocumentSelector` and `updateDocuments()` unchanged. `askQuestion()` preserved on `ConversationService` for non-streaming callers.

---

# P26-SLICE-04 — OpenAPI & Documentation

| Item | Location |
| ---- | -------- |
| `ConversationStreamEvent` schema | `Presentation/OpenApi/Schema/ConversationStreamEvent.php` |
| Controller annotations | `AskConversationChatStreamController` — `#[OA\Post]`, tag `Chat` |
| Nelmio alias | `nelmio_api_doc.yaml` |
| Architecture docs | `docs/architecture/README.md`, `architecture-rules.md`, `openapi.md` |

OpenAPI tests verify path existence, `ChatRequest` body, 200 `text/event-stream`, 400 `ErrorResponse`, `ConversationStreamEvent` schema, and reuse of `ChatStreamToken`.

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
| Gemini true streaming | Mock provider streams tokens; Gemini adapter does not yet stream natively |
| Cancellation | No client abort / server-side stream cancellation |
| Partial persistence | Conversation saved only after full stream completes |
| Citations in stream | Sources/citations not emitted as separate SSE events; use sync `POST …/chat` for full `ChatAnswer` |
| Conversation list | No `GET` endpoint to list conversations for a content |
| Provider thread context | RAG uses current question only; full thread in provider is future work |

---

# Future work

| Item | Rationale |
| ---- | --------- |
| Gemini streaming transport | True token-by-token from provider |
| Stream cancellation | Abort in-flight generation |
| Streaming citations | Emit sources/citations as SSE events or trailing metadata |
| Conversation list / rename / delete | Conversation management UX |
| Document weighting | Prioritize primary document in multi-doc RAG |
| Persistent vector index | Faster cross-document retrieval at scale |

---

# Documentation tree

```text
docs/
├── architecture/
│   ├── README.md              (Platform Sprint 26 section added)
│   ├── architecture-rules.md  (conversation streaming layer)
│   └── openapi.md             (POST …/conversations/{id}/chat/stream)
└── reports/
    ├── Sprint25-Verification.md
    └── Sprint26-Verification.md
```

---

# Platform capabilities after Sprint 26

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
| Conversation Streaming | ✅ |
| Frontend Conversation Streaming | ✅ |
