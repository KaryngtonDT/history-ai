# Learning State (Session Scope)

Sprint 60 introduces **session learning state** under `Domain/Shadow/SessionLearning/`.

This is distinct from Sprint 57 `Domain/Learning/LearningProfile` (cross-session profile).

## Aggregate

`SessionLearningState` tracks:

| Dimension | Values |
|-----------|--------|
| Attention | high, medium, low |
| Fatigue | low, medium, high |
| Confidence | growing, stable, struggling |
| Pace | fast, normal, slow |
| Energy | high, medium, low |
| Difficulty | easy, intermediate, advanced |

## Observations

`SessionObservation` records explicit watch events:

- pause, resume, question, repeated_question
- replay, skip, challenge_success, slow_response, fast_response

## Adjustments

`StrategyAdjustment` provides explainability:

```
10:32 — Strategy: example_driven
reason: attention=low fatigue=medium confidence=stable
```

## Precedence

```
User opt-out
  ↓
Session TeachingStrategy (Sprint 60)
  ↓
Shadow Identity (Sprint 58)
  ↓
Learning profile hints (Sprint 57)
  ↓
Defaults
```
