# Shadow Memory Timeline

Platform Sprint 62 adds durable **learning memory** across Shadow watch sessions.

Shadow now recalls prior concepts, vocabulary, milestones, and knowledge connections when answering questions — without modifying Sprint 60 session learning or Sprint 61 relationship traits.

## Principles

- Pedagogical memory only (concepts, vocabulary, milestones, questions)
- Deterministic concept extraction and recall — no model training
- Composes with Relationship (S61), Identity (S58), Session Learning (S60)
- Full reset supported
- Explainable recall lines in prompts

## Precedence

```text
Opt-out
  → Manual edits
  → Explicit identity preference
  → Confirmed relationship trait
  → Memory recall (Sprint 62)
  → Session strategy (Sprint 60)
  → Defaults
```

## Flow

```text
Watch question → MemoryBuilder → MemoryTimeline
  → KnowledgeRecallEngine → MemoryContextComposer → ShadowWatchPromptBuilder
  → Memory Explorer UI
```

## API

```text
GET  /api/shadow/memory/timeline
GET  /api/shadow/memory/concepts
GET  /api/shadow/memory/vocabulary
GET  /api/shadow/memory/milestones
GET  /api/shadow/memory/connections
GET  /api/shadow/memory/journey
POST /api/shadow/memory/search
POST /api/shadow/memory/reset
```

## Persistence

`storage/shadow/memory/{timelineId}.json` via `JsonFileStore`.

## Frontend

`/settings/shadow/memory` — Learning Journey, concepts, connections, timeline, search, reset.
