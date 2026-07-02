# Shadow Emotional Intelligence

Platform Sprint 60 adds **session-scoped pedagogical adaptation** for Shadow watch mode.

Shadow observes learning signals during a session (pauses, repeated questions, replays, response pace) and adapts teaching strategy deterministically — without psychological inference or opaque model training.

## Principles

- Pedagogical enums only (`struggling`, `high fatigue`) — no emotion labels
- Deterministic analyzers and explicit strategy adjustments
- Opt-in per session (`adaptiveEnabled`)
- Resettable session state (independent from Sprint 57 learning profile)
- Composes with Shadow Identity (58) and Learning profile (57)

## Flow

```
Watch events → SessionLearningState → TeachingStrategyResolver
    → ShadowWatchPromptBuilder / browser TTS pace
```

## API

```
GET  /api/videos/{videoId}/shadow/sessions/{sessionId}/learning
PUT  /api/videos/{videoId}/shadow/sessions/{sessionId}/learning/preferences
GET  /api/videos/{videoId}/shadow/sessions/{sessionId}/strategy
POST /api/videos/{videoId}/shadow/sessions/{sessionId}/learning/observations
```

## Persistence

`storage/shadow/session-learning/{sessionId}.json` via `JsonFileStore` (Sprint 59 pattern).
