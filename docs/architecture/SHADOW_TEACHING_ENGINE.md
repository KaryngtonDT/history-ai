# Shadow Teaching Engine

Platform Sprint 63 — Shadow becomes a **personal teacher** with paths, exercises, revisions, and checkpoints.

## Composition

```text
Memory Timeline (S62)
Relationship (S61)
Adaptive Learning (S57)
        │
        ▼
TeachingPlanner → TeachingPlan
        │
        ├── LearningPathBuilder
        ├── ExercisePlanner
        ├── RevisionPlanner
        ├── CheckpointGenerator
        └── TeachingAdvisor
        │
        ▼
TeachingContextComposer → ShadowWatchPromptBuilder
```

## Principles

- Deterministic planning — no model training
- Explainable objectives, revisions, and recommendations
- User preferences (voice mode, difficulty, revision toggle)
- Full reset supported
- Does not modify S60–S62 domains

## API

See [docs/shadow/API.md](../shadow/API.md).

## Persistence

`storage/shadow/teaching/{planId}.json`

## Frontend

`/settings/shadow/teaching` + Watch `ShadowTeachingPanel`
