# Platform Sprint 28 — Final Verification Report

Version: 1.0

Status: Accepted

Date: 2026-06-29

---

# Executive summary

Platform Sprint 28 delivers **Agent Workflows** end to end: agent domain model, deterministic planner, executor trace projection, HTTP API, frontend agent mode panel, and OpenAPI documentation. Slice 5 changed **OpenAPI documentation, architecture docs, and verification only** — no business logic in backend handlers, frontend, or worker.

| Area | Result |
| ---- | ------ |
| Backend PHPUnit | ✅ 936 tests, 3176 assertions |
| Backend architecture | ✅ 36 tests, 45 assertions |
| Backend OpenAPI | ✅ 60 tests, 670 assertions |
| Frontend build | ✅ OK |
| Frontend Vitest | ✅ 525 tests (99 files) |
| Frontend Biome | ✅ clean (465 files) |
| Worker pytest | ✅ 127 tests |
| Worker Ruff | ✅ All checks passed |
| Agent run API | ✅ Documented; behavior unchanged |
| Frontend agent mode | ✅ Documented; behavior unchanged |

---

# Platform Sprint 28 scope (slices 01–05)

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| P28-SLICE-01 | `AgentTool`, `AgentPlan`, `AgentStep`, collections | ✅ |
| P28-SLICE-02 | `DeterministicAgentPlanner`, keyword-based plan expansion | ✅ |
| P28-SLICE-03 | `RunAgentHandler`, execution trace DTOs (no real tools) | ✅ |
| P28-SLICE-04 | `POST …/agent/run`; `AgentModePanel` + execution trace UI | ✅ |
| P28-SLICE-05 | OpenAPI schemas, architecture docs, this report | ✅ |

---

# Final architecture

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
RunAgentHandler
        │
        ▼
AgentPlannerInterface → DeterministicAgentPlanner
        │
        ▼
AgentExecutionResult (plan[], steps[], finalSummary)
        │
        ▼
AgentExecutionTrace UI
```

The planner selects tools from question keywords (comparison → `knowledge_graph`, memory → `conversation_memory`). The executor records deterministic step summaries without invoking Semantic Search, Knowledge Graph, Conversation Memory, or Multi-Document Chat backends.

---

# P28-SLICE-01 — Agent Domain

| Component | Role |
| --------- | ---- |
| `AgentTool` | Enum: `semantic_search`, `knowledge_graph`, `conversation_memory`, `multi_document_chat` |
| `AgentStep` | Planned step with order, tool, description |
| `AgentPlan` | Immutable ordered plan |
| `AgentStepCollection` | Validated step list |
| `InvalidAgentPlanException` | Domain validation failures |

Domain-only; no HTTP, persistence, or real tool execution.

---

# P28-SLICE-02 — Deterministic Agent Planner

| Component | Role |
| --------- | ---- |
| `AgentRequest` | Validated question value object |
| `AgentPlannerInterface` | Domain port for plan generation |
| `DeterministicAgentPlanner` | Keyword-based plan: default `semantic_search` + `multi_document_chat`; optional `knowledge_graph` / `conversation_memory` |

No LLM planner; plans are reproducible from question text alone.

---

# P28-SLICE-03 — Agent Executor

| Component | Role |
| --------- | ---- |
| `RunAgentHandler` | Plans and executes trace projection |
| `AgentExecutionResult` | Domain result with plan, steps, final summary |
| `AgentExecutionStatus` | `completed`, `skipped`, `failed` |
| Application DTOs | `AgentPlanStepResult`, `AgentExecutionStepResult`, `AgentExecutionResultDto` |

Executor marks steps `completed` with prepared summaries; no real tool calls.

---

# P28-SLICE-04 — Agent API & Frontend Agent Mode

| Component | Role |
| --------- | ---- |
| `RunAgentController` | `POST /api/contents/{contentId}/agent/run` |
| `RunAgentRequest` | `question` (required), optional `conversationId` |
| `AgentExecutionResponse` | JSON: `plan[]`, `steps[]`, `finalSummary` |
| `AgentService` | Client validation; delegates to repository |
| `AgentModePanel` | Question form, loading/error states, trace display |
| `AgentExecutionTrace` | Plan + execution step UI |

Integrated in `ProcessingArtifacts` below `KnowledgeGraphPanel`. `conversationId` prop reserved for future ChatPanel wiring.

---

# P28-SLICE-05 — OpenAPI & Documentation

| Item | Location |
| ---- | -------- |
| `AgentRunRequest` schema | `Presentation/OpenApi/Schema/AgentRunRequest.php` |
| `AgentExecution` schema | `Presentation/OpenApi/Schema/AgentExecution.php` |
| `AgentPlanStep` schema | `Presentation/OpenApi/Schema/AgentPlanStep.php` |
| `AgentExecutionStep` schema | `Presentation/OpenApi/Schema/AgentExecutionStep.php` |
| `AgentTool` enum | `Presentation/OpenApi/Schema/AgentToolSchema.php` |
| `AgentExecutionStatus` enum | `Presentation/OpenApi/Schema/AgentExecutionStatusSchema.php` |
| Controller annotations | `RunAgentController` |
| Nelmio aliases | `nelmio_api_doc.yaml` |
| Architecture docs | `docs/architecture/README.md`, `architecture-rules.md`, `openapi.md` |

OpenAPI tests verify path, request body (`AgentRunRequest`), response 200 (`AgentExecution`), response 400 (`ErrorResponse`), enum schemas, and `plan[]` / `steps[]` / `finalSummary` properties.

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

All suites passed on 2026-06-29 after backend container sync.

---

# Known limitations

| Topic | Current state |
| ----- | ------------- |
| Planner | Deterministic keyword rules only; no LLM planner |
| Tool execution | Executor does not call real Semantic Search, Graph, Chat, or Memory tools |
| Persistence | Agent runs are not stored |
| Streaming | No SSE or progressive agent execution |
| Conversation wiring | `AgentModePanel` accepts `conversationId`; processing page omits it for this sprint |
| Citations | No tool result citations in agent trace |

---

# Future work

| Item | Rationale |
| ---- | --------- |
| Real Semantic Search tool execution | Ground agent steps in retrieved chunks |
| Real Knowledge Graph tool execution | Use graph neighborhood in comparison workflows |
| Conversation memory tool | Include persisted conversation context in plans |
| Agent streaming | Progressive plan/step updates in UI |
| Tool result citations | Link execution summaries to artifact/chunk sources |
| Persisted agent runs | Audit and replay agent workflows |
| LLM planner | Dynamic tool selection beyond keyword rules |

---

# Documentation tree

```text
docs/
├── architecture/
│   ├── README.md              (Platform Sprint 28 section added)
│   ├── architecture-rules.md  (AgentService, AgentModePanel, agent API)
│   └── openapi.md             (POST …/agent/run + agent schemas)
└── reports/
    ├── Sprint27-Verification.md
    └── Sprint28-Verification.md
```

---

# Platform capabilities after Sprint 28

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
| Knowledge Graph (content) | ✅ |
| Graph Neighborhood Explorer | ✅ |
| Conversation-Scoped Graph | ✅ |
| Deterministic Agent Workflows | ✅ |
| Frontend Agent Mode Panel | ✅ |

Sprint 29 can focus on **real agent tool execution**, building on this deterministic workflow foundation.
