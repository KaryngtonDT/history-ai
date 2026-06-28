# UX-02 — Final Verification Report

Version: 1.0

Status: Accepted

Date: 2026-06-26

---

# Executive summary

UX-02 introduced **interactive citations** to the RAG chat experience: numbered references in answers, API `citations[]` metadata, frontend mapping, and click-to-scroll navigation with temporary artifact highlight. Slice 5 changed **OpenAPI documentation and verification only** — no business logic in backend handlers, frontend components, or worker.

| Area | Result |
| ---- | ------ |
| Backend PHPUnit | ✅ 623 tests, 2077 assertions |
| Backend architecture | ✅ 35 tests, 43 assertions |
| Backend OpenAPI | ✅ 31 tests, 362 assertions |
| Frontend build | ✅ OK |
| Frontend Vitest | ✅ 410 tests (86 files) |
| Frontend Biome | ✅ clean (429 files) |
| Worker pytest | ✅ 127 tests |
| Worker Ruff | ✅ All checks passed |
| Chat citations contract | ✅ Documented; behavior unchanged |

---

# UX-02 scope (slices 01–05)

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| UX-02-SLICE-01 | Domain `ChatCitation`, `ChatCitationCollection`; mock provider markers | ✅ |
| UX-02-SLICE-02 | Application `ChatCitationResult`; JSON `citations[]` on chat API | ✅ |
| UX-02-SLICE-03 | Frontend citation mapping (`types`, repositories) | ✅ |
| UX-02-SLICE-04 | Interactive navigation (`[1]` click → scroll + highlight) | ✅ |
| UX-02-SLICE-05 | OpenAPI `ChatCitation` schema, architecture docs, this report | ✅ |

---

# Final architecture

```text
ChatProviderInterface
        │
        ▼
ChatResponse (answer + sources + citations)
        │
        ▼
ChatAnswerResult / ChatAnswer JSON
        │
        ▼
Frontend ChatService
        │
        ▼
ChatPanel → ChatMessageList
        │
        ├── ChatMessage ([1] buttons)
        └── SourcesPanel (clickable rows)
        │
        ▼
ProcessingArtifacts
        │
        ├── scrollIntoView(#artifact-{type})
        └── .history-ai-highlight (3s)
```

---

# Citation domain model

| Type | Role |
| ---- | ---- |
| `ChatCitation` | Immutable numbered reference (`number`, `ChatSource`) |
| `ChatCitationCollection` | Ordered list; validates sequential numbering 1..n |
| `ChatResponse.citations()` | Exposed alongside `answer()` and `sources()` |

Mock provider emits deterministic markers: `Mock answer based on retrieved context [1][2].` when sources exist.

---

# API contract

```json
{
  "answer": "Mock answer based on retrieved context [1].",
  "sources": [
    {
      "artifactId": "550e8400-e29b-41d4-a716-446655440002",
      "chunkId": "550e8400-e29b-41d4-a716-446655440010",
      "text": "## Ancient Rome",
      "score": 0.87
    }
  ],
  "citations": [
    {
      "number": 1,
      "artifactId": "550e8400-e29b-41d4-a716-446655440002",
      "chunkId": "550e8400-e29b-41d4-a716-446655440010",
      "score": 0.87
    }
  ]
}
```

**Design choice:** `citations[]` omits `text` — the frontend resolves `chunkId` against `sources[]` to avoid JSON duplication.

---

# Interactive navigation (frontend)

| Step | Behavior |
| ---- | -------- |
| Parse answer | Regex `/\[(\d+)\]/g` renders clickable `[1]` buttons |
| Click citation | Emits `{ chunkId, artifactId }` |
| Scroll | `scrollIntoView({ behavior: 'smooth' })` on `#artifact-{type}` |
| Highlight | CSS class `history-ai-highlight` for 3 seconds, then removed |

Implemented in `frontend/src/features/chat/citationNavigation.ts`. No fetch, no backend calls on navigation.

---

# OpenAPI documentation (slice 5)

| Schema | Location | Purpose |
| ------ | -------- | ------- |
| `ChatCitation` | `Presentation/OpenApi/Schema/ChatCitation.php` | Citation entry (`number`, `artifactId`, `chunkId`, `score`) |
| `ChatAnswer` | `Presentation/OpenApi/Schema/ChatAnswer.php` | Response envelope (`answer`, `sources[]`, `citations[]`) |
| `ChatSource` | `Presentation/OpenApi/Schema/ChatSource.php` | Source entry with excerpt text (unchanged) |
| `ChatRequest` | `Presentation/OpenApi/Schema/ChatRequest.php` | Request body (unchanged) |

Controller: `#[OA\Post]` on `AskContentChatController` references `#/components/schemas/ChatAnswer`.

Nelmio alias `ChatCitation` registered in `backend/config/packages/nelmio_api_doc.yaml`.

OpenAPI tests verify: schema existence, required fields, score range 0..1, `ChatAnswer.citations` array ref.

---

# Architecture rules

## Backend

| Rule | Status |
| ---- | ------ |
| OpenAPI schemas in `Presentation/OpenApi` only | ✅ |
| No domain logic changes in slice 5 | ✅ |
| Chat HTTP contract additive (`citations[]`) | ✅ |

## Frontend

| Rule | Status |
| ---- | ------ |
| Citation mapping in service layer only | ✅ |
| `ChatMessage` does not import `@/services/` | ✅ |
| Navigation via callback from `ProcessingArtifacts` | ✅ |

Documented in `docs/architecture/architecture-rules.md`.

---

# Documentation verification

| Document | Location | Status |
| -------- | -------- | ------ |
| Architecture rules | `docs/architecture/architecture-rules.md` | ✅ interactive citations section |
| Architecture README | `docs/architecture/README.md` | ✅ UX-02 complete (slices 01–05) |
| OpenAPI notes | `docs/architecture/openapi.md` | ✅ `ChatCitation` + chat endpoint |
| UX-02 report | `docs/reports/UX02-Verification.md` | ✅ This document |

---

# Validation summary

## Backend

```bash
docker compose exec backend php bin/phpunit
```

```
OK (623 tests, 2077 assertions)
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
OK (31 tests, 362 assertions)
```

## Frontend

```bash
docker compose exec frontend npm run build
docker compose exec frontend npm test
docker compose exec frontend npm run check
```

```
npm run build  ✓ built
npm test       410 passed (86 files)
npm run check  Checked 429 files. No fixes applied.
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

# Test count delta (UX-01 complete → UX-02 complete)

| Suite | UX-01 complete | UX-02 complete | Delta |
| ----- | -------------- | -------------- | ----- |
| Backend PHPUnit | 615 | 623 | +8 |
| Backend OpenAPI assertions | 342 | 362 | +20 |
| Frontend Vitest | 386 | 410 | +24 |
| Worker pytest | 127 | 127 | — |

New tests: citation domain/collection, API mapping, frontend citation types/repos, `citationNavigation`, interactive UI, OpenAPI `ChatCitation` schema.

---

# Known limitations

| Limitation | Impact |
| ---------- | ------ |
| No chunk-level highlight | Navigation scrolls to artifact section, not exact chunk offset |
| No streaming | Full answer returned in one response |
| No conversation persistence | Each question is stateless |
| Gemini citations empty | Real LLM must parse `[n]` markers in a future slice |
| In-memory vector store | Corpus rebuilt per chat request |

---

# Future work

| Target | Rationale |
| ------ | --------- |
| **UX-03 — Streaming Chat** | Progressive answer display; extend transport without changing domain contract |
| **UX-04 — Conversation history** | Multi-turn memory; store chat sessions per content |
| Chunk-level highlight | Scroll to exact excerpt within artifact renderer |
| Gemini citation parsing | Populate `ChatCitationCollection` from LLM output |
| Persistent vector indexing | Remove per-request corpus rebuild |

Existing abstractions (`ChatProviderInterface`, `ChatRequest`/`ChatResponse`, `ChatService`, `citationNavigation`) support streaming and conversation memory with minimal architectural impact.

---

# CTO sign-off criteria

| Criterion | Status |
| --------- | ------ |
| `ChatCitation` documented in OpenAPI | ✅ |
| `ChatAnswer` documents `citations[]` | ✅ |
| Nelmio alias added | ✅ |
| OpenAPI tests green | ✅ |
| `UX02-Verification.md` generated | ✅ |
| No business logic changes in slice 5 | ✅ |
| All validation suites green | ✅ |
