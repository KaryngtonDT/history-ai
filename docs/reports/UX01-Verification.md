# UX-01 — Final Verification Report

Version: 1.0

Status: Accepted

Date: 2026-06-26

---

# Executive summary

UX-01 introduced an **interactive RAG chat** experience across backend, frontend, and OpenAPI documentation. Slice 8 changed **documentation and OpenAPI annotations only** — no business logic in backend handlers, frontend components, or worker.

| Area | Result |
| ---- | ------ |
| Backend PHPUnit | ✅ 615 tests, 2019 assertions |
| Backend architecture | ✅ 35 tests, 43 assertions |
| Backend OpenAPI | ✅ 31 tests, 342 assertions |
| Frontend build | ✅ OK |
| Frontend Vitest | ✅ 386 tests (84 files) |
| Frontend Biome | ✅ clean (424 files) |
| Worker pytest | ✅ 127 tests |
| Worker Ruff | ✅ All checks passed |
| Chat API contract | ✅ Documented; unchanged behavior |

---

# UX-01 scope (slices 01–08)

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| UX-01-SLICE-01 | Domain chat model (`ChatOrchestrator`, `ChatProviderInterface`) | ✅ |
| UX-01-SLICE-02 | Mock RAG chat API (`POST /api/contents/{contentId}/chat`) | ✅ |
| UX-01-SLICE-03 | Generalized `ChatRequest` / `ChatResponse` provider contract | ✅ |
| UX-01-SLICE-04 | Optional `GeminiChatProvider` adapter | ✅ |
| UX-01-SLICE-05 | `ChatProviderFactory`; `CHAT_PROVIDER` env selection | ✅ |
| UX-01-SLICE-06 | Frontend `ChatService` + repository layer | ✅ |
| UX-01-SLICE-07 | Frontend `ChatPanel` UI in `ProcessingArtifacts` | ✅ |
| UX-01-SLICE-08 | OpenAPI schemas, architecture docs, this report | ✅ |

---

# Final architecture

```text
Question
     │
     ▼
POST /api/contents/{contentId}/chat
     │
     ▼
AskContentChatHandler
     │
     ▼
SemanticRetriever
     │
     ▼
ChatContext
     │
     ▼
ChatOrchestrator
     │
     ▼
ChatProviderInterface
     │
     ├── MockChatProvider (default)
     └── GeminiChatProvider
     │
     ▼
ChatResponse
     │
     ▼
Frontend ChatService
     │
     ▼
ChatPanel
        │
        ├── ChatMessageList
        ├── ChatMessage
        ├── ChatInput
        └── SourcesPanel
```

---

# Chat provider (domain contract)

```text
ChatRequest (prompt + sources + options)
        │
        ▼
ChatProviderInterface.answer()
        │
        ▼
ChatResponse (answer text)
```

| Type | Role |
| ---- | ---- |
| `ChatProviderInterface` | Domain port — `answer(ChatRequest): ChatResponse` |
| `ChatRequest` | Prompt, retrieved sources, optional model options |
| `ChatResponse` | Generated answer text |
| `ChatOrchestrator` | Builds context from semantic retrieval; delegates to provider |

Domain lives in `backend/src/Domain/Chat/` with no Symfony, HTTP, network, or AI SDK dependencies.

---

# MockChatProvider

```text
Infrastructure/Chat/MockChatProvider
        │
        implements ChatProviderInterface
        │
        ├── Returns deterministic mock answer
        └── Default for local dev and CI
```

- No external dependencies.
- Wired as default via `ChatProviderFactory` when `CHAT_PROVIDER=mock` (or unset).
- Mock answer: `"Mock answer based on retrieved context."`

---

# GeminiChatProvider

```text
Infrastructure/Chat/GeminiChatProvider
        │
        implements ChatProviderInterface
        │
        ├── Gemini generateContent REST API
        ├── GeminiChatTransportInterface (curl in production)
        └── requires GEMINI_API_KEY when selected
```

| Env var | Default | Purpose |
| ------- | ------- | ------- |
| `GEMINI_API_KEY` | (empty) | API authentication |
| `GEMINI_CHAT_MODEL` | `gemini-2.5-flash` | Model resource name |

- Optional — not default in runtime or CI.
- Unit tests use mocked transport; no live network in automated tests.
- Provider failures throw `GeminiChatProviderException` (infrastructure), not domain exceptions.

---

# ChatProviderFactory

```text
CHAT_PROVIDER=mock|gemini
        │
        ▼
ChatProviderFactory.create()
        │
        ├── mock → MockChatProvider
        ├── gemini → GeminiChatProvider (requires GEMINI_API_KEY)
        └── unknown → InvalidChatProviderConfigurationException
```

Wiring (`services.yaml`):

```text
ChatProviderInterface → factory: [ChatProviderFactory, create]
```

| `CHAT_PROVIDER` | Result |
| --------------- | ------ |
| `mock` (default) | Deterministic mock answer |
| `gemini` | Gemini provider (API key required at factory resolution) |
| unknown | Configuration exception |

No network call during factory selection.

---

# Frontend panel

```text
ProcessingArtifacts
        │
        ▼
ChatPanel (only component using ChatService)
        │
        ├── ChatMessageList   (props-only)
        ├── ChatMessage       (props-only)
        ├── ChatInput         (props-only)
        └── SourcesPanel      (props-only)
```

| Rule | Enforcement |
| ---- | ----------- |
| `ChatPanel` → `chatService.askQuestion()` only | Component tests + source scan |
| No `HttpChatRepository` in `features/chat` | `findFeatureChatTransportViolations()` |
| No extra `artifactService.listByContentId()` | `ProcessingArtifacts` integration test |
| Enter submits; Shift+Enter newline | `ChatInput` tests |
| Sources use `getArtifactAnchor()` | `SourcesPanel` tests |

---

# OpenAPI documentation (slice 8)

| Schema | Location | Purpose |
| ------ | -------- | ------- |
| `ChatRequest` | `Presentation/OpenApi/Schema/ChatRequest.php` | Request body (`question`, 1–2000 chars) |
| `ChatAnswer` | `Presentation/OpenApi/Schema/ChatAnswer.php` | Response envelope (`answer`, `sources[]`) |
| `ChatSource` | `Presentation/OpenApi/Schema/ChatSource.php` | Source entry (`artifactId`, `chunkId`, `text`, `score`) |

Controller annotation: `#[OA\Post]` on `AskContentChatController` with `operationId: askContentChat`, tag `Chat`, responses 200 and 400.

Nelmio aliases registered in `backend/config/packages/nelmio_api_doc.yaml`.

**Note:** Chat sources omit the `position` field present on semantic-search `RetrievedChunk` entries.

---

# Architecture rules

## Backend

| Rule | Scope | Enforced by |
| ---- | ----- | ----------- |
| Chat domain purity | `Domain/Chat` | `testChatDomainDoesNotDependOnOuterLayersOrFrameworks` |
| Chat application | `Application/Chat` | `testChatApplicationMayDependOnChatSemanticArtifactAndContentDomainOnly` |
| Chat HTTP presentation | Controller + Request + Response | `testChatPresentationMayDependOnChatApplicationOnly` |
| Chat infrastructure | `Infrastructure/Chat` | `testChatInfrastructureMayDependOnChatDomainOnly` |
| OpenAPI schemas | `Presentation/OpenApi` only | No domain/application imports |

## Frontend

| Rule | Status |
| ---- | ------ |
| `ChatPanel` → `ChatService` only | ✅ |
| Props-only subcomponents | ✅ |
| No `HttpChatRepository` in `features/chat` | ✅ |
| No `fetch()` in feature components | ✅ |

Documented in `docs/architecture/architecture-rules.md`.

---

# Documentation verification

| Document | Location | Status |
| -------- | -------- | ------ |
| Architecture rules | `docs/architecture/architecture-rules.md` | ✅ chat provider + frontend panel sections |
| Architecture README | `docs/architecture/README.md` | ✅ UX-01 complete (slices 01–08) |
| OpenAPI notes | `docs/architecture/openapi.md` | ✅ chat endpoint + schemas |
| UX-01 report | `docs/reports/UX01-Verification.md` | ✅ This document |

---

# Validation summary

## Backend

```bash
docker compose exec backend php bin/phpunit
```

```
OK (615 tests, 2019 assertions)
```

```bash
docker compose exec backend php bin/phpunit tests/Architecture
```

```
OK (35 tests, 43 assertions)
```

```bash
docker compose exec backend php bin/phpunit tests/Functional/OpenApi
```

```
OK (31 tests, 342 assertions)
```

## Frontend

```bash
docker compose exec frontend npm run build
docker compose exec frontend npm test
docker compose exec frontend npm run check
```

```
npm run build  ✓ built
npm test       386 passed (84 files)
npm run check  Checked 424 files. No fixes applied.
```

## Worker

```bash
docker compose exec worker pytest
docker compose exec worker ruff check .
```

```
127 passed, 1 warning
All checks passed!
```

---

# Test count delta (pre-UX-01 → UX-01 complete)

| Suite | Pre-UX-01 (Sprint 22) | UX-01 complete | Delta |
| ----- | --------------------- | -------------- | ----- |
| Backend PHPUnit | 544 | 615 | +71 |
| Backend architecture | 31 | 35 | +4 |
| Backend OpenAPI | 27 | 31 | +4 |
| Frontend Vitest | 348 | 386 | +38 |
| Worker pytest | 127 | 127 | — |

New backend tests: chat domain, handler, controller, providers, factory, OpenAPI chat schemas. New frontend tests: `ChatService`, chat panel components, architecture guard, `ProcessingArtifacts` integration.

---

# Known limitations

| Limitation | Impact |
| ---------- | ------ |
| No streaming | Full answer returned in one response |
| No conversation persistence | Each question is stateless |
| No multi-turn memory | Follow-up questions lack prior context |
| In-memory vector store | Corpus rebuilt per semantic/chat request |
| Mock provider default | Production-quality answers require `CHAT_PROVIDER=gemini` |
| No markdown rendering | Chat UI shows plain text bubbles |
| Gemini not default | Real LLM answers require explicit config |

---

# Future work

| Target | Rationale |
| ------ | --------- |
| **UX-02 — Streaming Chat** | Progressive answer display; extend transport without changing domain contract |
| Conversation persistence | Store chat history per content/user |
| Multi-turn memory | Pass prior turns in `ChatRequest` context |
| Persistent vector indexing | Remove per-request corpus rebuild (Sprint 23 roadmap) |
| Markdown rendering | Richer answer formatting in `ChatMessage` |

The existing abstractions (`ChatProviderInterface`, `ChatProviderFactory`, `ChatRequest`/`ChatResponse`, `ChatService`) allow streaming and provider swaps with minimal architectural impact.

---

# CTO sign-off criteria

| Criterion | Status |
| --------- | ------ |
| `POST /api/contents/{contentId}/chat` documented | ✅ |
| Schemas `ChatRequest`, `ChatAnswer`, `ChatSource` | ✅ |
| OpenAPI tests green | ✅ |
| UX-01 documentation complete | ✅ |
| `UX01-Verification.md` generated | ✅ |
| No business logic changes in slice 8 | ✅ |
| All validation suites green | ✅ |
