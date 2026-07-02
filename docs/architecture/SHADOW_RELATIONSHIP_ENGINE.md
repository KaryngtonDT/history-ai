# Shadow Relationship Engine

Platform Sprint 61 adds a durable, explainable **relationship model** between Shadow and the learner.

Shadow no longer adapts only to how a session is going (Sprint 60). It also composes durable learner traits from signals across:

- Shadow watch sessions
- Learning profile (Sprint 57)
- Shadow identity preferences (Sprint 58)
- Explicit user utterances

## Principles

- Pedagogical traits only (`interest`, `habit`, `motivator`, `communication`)
- Deterministic detectors — no psychology labels, no model training
- User approval before inferred traits become durable (default)
- Manual override priority over inferred signals
- Full reset supported
- Composes with Session Learning (60) without modifying it

## Precedence

```text
Opt-out
  → Approved pending change
  → Manual trait edit/remove
  → Explicit identity preference
  → Confirmed relationship trait
  → Hypothesis trait (optional display)
  → Learning profile
  → Session strategy (Sprint 60)
  → Defaults
```

## Flow

```text
Signals → RelationshipEvolutionEngine → RelationshipProfile
  → RelationshipContextComposer → ShadowWatchPromptBuilder
  → Relationship Portrait UI
```

## API

```text
GET  /api/shadow/relationship/profile
GET  /api/shadow/relationship/portrait
GET  /api/shadow/relationship/timeline
GET  /api/shadow/relationship/interests
POST /api/shadow/relationship/signals
POST /api/shadow/relationship/preferences
POST /api/shadow/relationship/configure
POST /api/shadow/relationship/changes/{id}/approve
POST /api/shadow/relationship/changes/{id}/reject
POST /api/shadow/relationship/reset
```

## Persistence

`storage/shadow/relationship/{profileId}.json` via `JsonFileStore`.

## Frontend

`/settings/shadow/relationship` — Relationship Portrait, interests, habits, timeline, conversational teaching with approval.
