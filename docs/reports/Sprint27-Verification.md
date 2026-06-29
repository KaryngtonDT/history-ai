# Platform Sprint 27 — Final Verification Report

Version: 1.0

Status: Accepted

Date: 2026-06-26

---

# Executive summary

Platform Sprint 27 delivers **Knowledge Graph Explorer 2.0** end to end: graph domain collections and neighborhood projection, neighborhood API, interactive frontend explorer, conversation-scoped graph API, and OpenAPI documentation. Slice 5 changed **OpenAPI documentation, architecture docs, and verification only** — no business logic in backend handlers, frontend, or worker.

| Area | Result |
| ---- | ------ |
| Backend PHPUnit | ✅ 856 tests, 2945 assertions |
| Backend architecture | ✅ 36 tests, 45 assertions |
| Backend OpenAPI | ✅ 56 tests, 605 assertions |
| Frontend build | ✅ OK |
| Frontend Vitest | ✅ 503 tests (93 files) |
| Frontend Biome | ✅ clean (447 files) |
| Worker pytest | ✅ 127 tests |
| Worker Ruff | ✅ All checks passed |
| Neighborhood API | ✅ Documented; behavior unchanged |
| Conversation graph API | ✅ Documented; behavior unchanged |
| Frontend graph UX | ✅ Documented; behavior unchanged |

---

# Platform Sprint 27 scope (slices 01–05)

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| P27-SLICE-01 | `GraphNodeCollection`, `GraphEdgeCollection`, `GraphNeighborhood`, `neighborsOf()` | ✅ |
| P27-SLICE-02 | `GET …/graph/artifacts/{artifactId}/neighborhood` | ✅ |
| P27-SLICE-03 | `GraphService.getGraphNeighborhood()`; interactive `KnowledgeGraphPanel` | ✅ |
| P27-SLICE-04 | `GET /api/conversations/{conversationId}/graph` | ✅ |
| P27-SLICE-05 | OpenAPI schemas, architecture docs, this report | ✅ |

---

# Final architecture

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

Content-level graph (Sprint 17) remains available. Neighborhood returns one-hop projection with `label` on nodes and optional `weight` on edges. Conversation graph merges artifacts from `conversation.documents[]` in document order.

---

# P27-SLICE-01 — Graph Domain

| Component | Role |
| --------- | ---- |
| `GraphNodeCollection` | Validated, immutable node list |
| `GraphEdgeCollection` | Validated, immutable edge list |
| `GraphNeighborhood` | Center node, neighbor nodes, connecting edges |
| `KnowledgeGraph::neighborsOf()` | One-hop neighborhood projection |
| `InvalidKnowledgeGraphException` | Domain validation failures |

Domain-only; no persistence or graph database.

---

# P27-SLICE-02 — Neighborhood API

| Component | Role |
| --------- | ---- |
| `GetGraphNeighborhoodHandler` | Resolves content graph, projects neighborhood |
| `GetGraphNeighborhoodController` | `GET …/graph/artifacts/{artifactId}/neighborhood` |
| `GraphNeighborhoodResponse` | JSON: `center`, `neighbors[]`, `edges[]` |

**Response:** `GraphNeighborhood` — nodes use `label`; edges include `weight`.

**Errors:** HTTP 400 for invalid UUID; HTTP 404 when artifact is not in the content graph.

---

# P27-SLICE-03 — Frontend Graph Neighborhood Explorer

| Component | Role |
| --------- | ---- |
| `GraphService.getGraphNeighborhood()` | Fetches neighborhood for highlight state |
| `KnowledgeGraphPanel` | Loads full graph; on node click loads neighborhood |
| `InteractiveGraph` | Props-only: selected node, neighbors, highlighted edges |

Node click highlights center, direct neighbors, and connecting edges. No shortest-path or multi-hop expansion.

---

# P27-SLICE-04 — Conversation-Aware Graph

| Component | Role |
| --------- | ---- |
| `GetConversationKnowledgeGraphHandler` | Builds graph from conversation document set |
| `GetConversationKnowledgeGraphController` | `GET /api/conversations/{conversationId}/graph` |
| `GraphService.getConversationGraph()` | Frontend repository call |
| `KnowledgeGraphPanel` | Optional `conversationId` prop switches data source |

When `conversationId` is set, the panel loads the conversation-scoped graph instead of the single-content graph. ChatPanel wiring for `conversationId` is deferred.

---

# P27-SLICE-05 — OpenAPI & Documentation

| Item | Location |
| ---- | -------- |
| `GraphNeighborhood` schema | `Presentation/OpenApi/Schema/GraphNeighborhood.php` |
| `GraphNeighborhoodNode` schema | `Presentation/OpenApi/Schema/GraphNeighborhoodNode.php` |
| `GraphEdge.weight` | `Presentation/OpenApi/Schema/GraphEdge.php` |
| Controller annotations | `GetGraphNeighborhoodController`, `GetConversationKnowledgeGraphController` |
| Nelmio aliases | `GraphNeighborhoodNode`, `GraphNeighborhood` in `nelmio_api_doc.yaml` |
| Architecture docs | `docs/architecture/README.md`, `architecture-rules.md`, `openapi.md` |

OpenAPI tests verify neighborhood and conversation graph paths, response schemas, `GraphEdge.weight`, and 400/404 `ErrorResponse` entries.

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
| Neighborhood depth | One-hop only; no multi-hop or recursive expansion |
| Shortest path | Not implemented |
| Graph algorithms | No ranking, clustering, or centrality |
| Graph persistence | Graphs built on demand from artifacts/relations |
| Graph database | No dedicated graph store |
| Conversation wiring | `KnowledgeGraphPanel` accepts `conversationId`; ChatPanel integration deferred |
| Node field naming | Content graph nodes use `title`; neighborhood nodes use `label` |

---

# Future work

| Item | Rationale |
| ---- | --------- |
| Shortest path | Navigate between distant artifacts |
| Graph ranking | Surface important nodes/edges |
| Graph clustering | Group related artifact communities |
| Graph search | Query nodes/edges by label or type |
| Graph analytics | Usage and structure metrics |
| Graph-backed agents | Agent workflows over knowledge graph (Sprint 28+) |
| Multi-hop neighborhood | Expand explorer beyond direct neighbors |
| Graph persistence / graph DB | Scale and query performance at volume |

---

# Documentation tree

```text
docs/
├── architecture/
│   ├── README.md              (Platform Sprint 27 section added)
│   ├── architecture-rules.md  (GraphService, KnowledgeGraphPanel, conversation graph)
│   └── openapi.md             (neighborhood + conversation graph endpoints)
└── reports/
    ├── Sprint26-Verification.md
    └── Sprint27-Verification.md
```

---

# Platform capabilities after Sprint 27

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

Sprint 28 can focus on **Agent Workflows**, building on this structured knowledge engine.
