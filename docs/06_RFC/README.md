# Request For Comments (RFC)

Version: 1.0

Status: Active

---

# Role

RFCs are **living architecture specifications**. They describe **domain and system behavior**, not framework choices.

A validated RFC becomes the **source of truth** for:

- Epics, Features, User Stories
- Implementation Tasks
- Acceptance tests
- Sequence and state diagrams
- API contracts and schema evolution

**No major aggregate, pipeline step, or cross-service contract is implemented without an accepted RFC.**

---

# Principles

1. **Domain first** — RFCs name business concepts, not Symfony, Redis, or MinIO.
2. **One concern per RFC** — RFC-0001 sets the pipeline vision; detail lives in follow-up RFCs.
3. **Alternatives required** — Every RFC documents options considered and why one was chosen.
4. **Tasks derive from RFC** — Tasks reference `RFC-XXXX, §section`; they do not invent architecture.

---

# Corpus

| RFC | Title | Status | Depends on |
| --- | ----- | ------ | ---------- |
| [RFC-0001](RFC-0001-content-processing-pipeline.md) | Content Processing Pipeline | **Proposed** | — |
| RFC-0002 | Content Model | Planned | RFC-0001 |
| RFC-0003 | ProcessingJob Lifecycle | Planned | RFC-0001 |
| RFC-0004 | Artifact Model | Planned | RFC-0001 |
| RFC-0005 | Storage Abstraction | Planned | RFC-0001 |
| RFC-0006 | Worker Architecture | Planned | RFC-0001 |
| RFC-0007 | AI Pipeline | Planned | RFC-0001 |
| RFC-0008 | Event Architecture | Planned | RFC-0001 |
| RFC-0009 | Learning Package | Planned | RFC-0001 |
| RFC-0010 | Multi-Agent Orchestration | Planned | RFC-0001 |

---

# RFC Lifecycle

```text
Draft        Author writes RFC; open questions listed
     ↓
Review       CTO / Principal Engineer review
     ↓
Accepted     Decision section filled; ADR(s) created
     ↓
Implemented  Tasks completed; code matches RFC
     ↓
Superseded   New RFC replaces (old RFC kept for history)
```

---

# Standard Structure

Every RFC uses this outline:

1. Problem Statement
2. Goals
3. Non-Goals
4. Domain Model
5. Processing Pipeline (if applicable)
6. State Machine (if applicable)
7. Aggregate Responsibilities
8. Sequence Diagrams
9. Alternatives Considered
10. Risks
11. Future Extensions
12. Decision

---

# Central Question (RFC-0001)

> **How does a piece of content become a learning package?**

Aligned with [PRODUCT_MANIFESTO](../00_PROJECT/PRODUCT_MANIFESTO.md): passive information → durable knowledge.

Everything between `Content` and `Learning Package` is the core of History AI.

---

# Documentation Map

```text
docs/
├── 00_PROJECT/       Vision, overview
├── 01_PRODUCT/         Epics, features, user stories
├── 02_ARCHITECTURE/    Blueprint, tech stack (frozen layout)
├── 03_AI/              AI agents and prompts
├── 04_EXECUTION/       Roadmap, implementation plan
├── 05_DECISIONS/       Accepted ADRs
├── 06_RFC/             Architecture proposals (this folder)
└── diagrams/           Shared Mermaid / diagram sources
```

---

# Current Focus

**RFC-0001 — Content Processing Pipeline** (Proposed)

Do not start TASK-0011 until RFC-0001 is **Accepted**.
