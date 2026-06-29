# UX-03 — Final Verification Report

Version: 1.0

Status: Accepted

Date: 2026-06-26

---

# Executive summary

UX-03 introduced **progressive streaming chat** to the RAG experience: domain stream model, streaming provider interface, mock SSE endpoint, frontend SSE service, and progressive UI in `ChatPanel`. Slice 6 changed **OpenAPI documentation and verification only** — no business logic in backend handlers, frontend components, or worker.

| Area | Result |
| ---- | ------ |
| Backend PHPUnit | ✅ 664 tests, 2194 assertions |
| Backend architecture | ✅ 35 tests, 43 assertions |
| Backend OpenAPI | ✅ 35 tests, 394 assertions |
| Frontend build | ✅ OK |
| Frontend Vitest | ✅ 430 tests (86 files) |
| Frontend Biome | ✅ clean (429 files) |
| Worker pytest | ✅ 127 tests |
| Worker Ruff | ✅ All checks passed |
| Streaming chat contract | ✅ Documented; behavior unchanged |

---

# UX-03 scope (slices 01–06)

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| UX-03-SLICE-01 | Domain `ChatToken`, `ChatStream`, `ChatStreamEvent`, collections | ✅ |
| UX-03-SLICE-02 | `StreamingChatProviderInterface`; `MockChatProvider` streamable | ✅ |
| UX-03-SLICE-03 | Mock SSE endpoint `POST /chat/stream` | ✅ |
| UX-03-SLICE-04 | Frontend `ChatService.streamQuestion()` + SSE parsing | ✅ |
| UX-03-SLICE-05 | Progressive assistant bubble in `ChatPanel` | ✅ |
| UX-03-SLICE-06 | OpenAPI `ChatStreamToken`, architecture docs, this report | ✅ |

---

# Final architecture

```text
ChatPanel
        │
        ▼
ChatService.streamQuestion()
        │
        ▼
HttpChatRepository (fetch + SSE parse)
        │
        ▼
POST /api/contents/{contentId}/chat/stream
        │
        ▼
AskContentChatStreamHandler (RAG pipeline)
        │
        ▼
StreamingChatProviderInterface → MockChatProvider
        │
        ▼
ChatStream → SSE token events
        │
        ▼
Assistant bubble grows token by token
```

Non-streaming path preserved:

```text
POST /chat → ChatProviderInterface → ChatAnswer JSON (sources + citations)
```

---

# Streaming domain model

| Type | Role |
| ---- | ---- |
| `ChatToken` | Immutable text fragment |
| `ChatStreamEvent` | Indexed event (`index`, `ChatToken`) |
| `ChatStreamEventCollection` | Ordered events; validates sequential index 0..n |
| `ChatStream` | Aggregates events; `toAnswer()` → `ChatAnswer` |

Domain has no knowledge of HTTP, SSE, Gemini, or React.

---

# Streaming provider interface

| Interface | Method | Returns |
| --------- | ------ | ------- |
| `ChatProviderInterface` | `answer()` | `ChatResponse` (unchanged) |
| `StreamingChatProviderInterface` | `stream()` | `ChatStream` |

`MockChatProvider` implements both. Gemini streaming transport is future work.

---

# SSE API contract

**Request:** same as non-streaming chat — `ChatRequest` (`question`, 1–2000 chars).

**Response:** `Content-Type: text/event-stream`

```text
event: token
data: {"index":0,"text":"Mock "}

event: token
data: {"index":1,"text":"answer "}

event: done
data: {}
```

Invalid UUID, malformed JSON, or invalid question → HTTP 400 `ErrorResponse`.

---

# Frontend streaming

| Layer | Responsibility |
| ----- | -------------- |
| `ChatService.streamQuestion()` | Validation; delegates to repository |
| `HttpChatRepository.streamQuestion()` | `fetch` POST + SSE parse (only allowed `fetch` outside `HttpClient`) |
| `MockChatRepository.streamQuestion()` | Deterministic token emission |
| `ChatPanel` | Progressive assistant bubble; submit disabled while streaming |

Enter submits; Shift+Enter newline. Error before first token → "Unable to generate answer."

---

# OpenAPI documentation (slice 6)

| Schema | Location | Purpose |
| ------ | -------- | ------- |
| `ChatStreamToken` | `Presentation/OpenApi/Schema/ChatStreamToken.php` | Token payload (`index`, `text`) |
| `ChatRequest` | Reused | Stream request body |
| `ErrorResponse` | Reused | 400 responses |

Controller: `#[OA\Post]` on `AskContentChatStreamController` documents `200 text/event-stream` and `400 ErrorResponse`.

Nelmio alias `ChatStreamToken` registered in `backend/config/packages/nelmio_api_doc.yaml`.

OpenAPI tests verify: path existence, request body ref, SSE content type, `ChatStreamToken` schema.

---

# Architecture rules

## Backend

| Rule | Status |
| ---- | ------ |
| OpenAPI schemas in `Presentation/OpenApi` only | ✅ |
| No domain logic changes in slice 6 | ✅ |
| Non-streaming `/chat` endpoint unchanged | ✅ |
| Domain/Chat pure; Application boundaries preserved | ✅ |

## Frontend

| Rule | Status |
| ---- | ------ |
| `ChatPanel` uses `ChatService` only | ✅ |
| `fetch` only in `HttpClient` + `HttpChatRepository` | ✅ |
| No feature imports `HttpChatRepository` | ✅ |
| Citation navigation unchanged (non-streaming metadata via `/chat` if needed) | ✅ |

Documented in `docs/architecture/architecture-rules.md`.

---

# Documentation verification

| Document | Location | Status |
| -------- | -------- | ------ |
| Architecture rules | `docs/architecture/architecture-rules.md` | ✅ streaming chat section |
| Architecture README | `docs/architecture/README.md` | ✅ UX-03 complete (slices 01–06) |
| OpenAPI notes | `docs/architecture/openapi.md` | ✅ `ChatStreamToken` + stream endpoint |
| UX-03 report | `docs/reports/UX03-Verification.md` | ✅ This document |

---

# Validation summary

## Backend

```bash
docker compose exec backend php bin/phpunit
```

```
OK (664 tests, 2194 assertions)
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
OK (35 tests, 394 assertions)
```

## Frontend

```bash
npm run build && npm test && npm run check
```

```
build OK
430 tests (86 files)
Biome clean (429 files)
```

## Worker

```bash
docker compose exec worker pytest
docker compose exec worker ruff check .
```

```
127 passed
All checks passed
```

---

# Known limitations

| Limitation | Notes |
| ---------- | ----- |
| Mock provider only for streaming | `StreamingChatProviderInterface` bound to `MockChatProvider`; Gemini not wired |
| No streaming citations | SSE emits text tokens only; sources/citations not in stream |
| Buffered SSE on frontend | `HttpChatRepository` reads full response body before parse (sufficient for mock) |
| No cancellation | Client cannot abort an in-flight stream via API |
| No conversation persistence | Messages live in React state only |

---

# Future work

| Item | Priority |
| ---- | -------- |
| Gemini true streaming transport | High — wire `GeminiChatProvider` to streaming API |
| Streaming citations | Medium — emit source/citation metadata after token stream |
| Client-side cancellation | Medium — `AbortController` on fetch |
| Conversation persistence | Low — store chat history server-side |
| Observability | Cross-cutting — latency metrics, structured logs (recommended sprint after UX-03) |

---

# UX-03 closure

With slice 6 complete, **UX-03 is closed**. The product delivers:

- RAG chat with retrieved context
- Interactive citations (UX-02)
- Progressive streaming answers (UX-03)

Next recommended focus: **foundation sprint** (metrics, observability, embedding/response cache, RAG performance) before additional UX features.
