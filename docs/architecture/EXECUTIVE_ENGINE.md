# Executive Engine

Deterministic orchestration layer for Sprint 66. See [SHADOW_EXECUTIVE_FUNCTION.md](SHADOW_EXECUTIVE_FUNCTION.md).

## Components

| Class | Responsibility |
|-------|----------------|
| `ExecutiveCoordinator` | Facade: sync, approve/reject/defer, reset |
| `ExecutivePlanner` | Builds plan from all Shadow inputs |
| `ExecutiveDecisionBuilder` | Creates typed decisions with reasons |
| `PriorityResolver` | Maps goal urgency + graph gaps → priority |
| `ReviewScheduler` | Surfaces overdue revisions (teaching + graph) |
| `LearningOpportunityDetector` | Patterns → opportunities |
| `ResourceRecommendationEngine` | Video/PDF/audio/exercise suggestions |
| `ExecutiveAgendaBuilder` | Today / upcoming task stacks |
| `DecisionExplanationBuilder` | Evidence chain for Why? UI |
| `EnergyAwarePlanner` | *(stretch)* Filters agenda by available minutes |
| `OpportunityEngine` | *(stretch)* Cross-surface learning signals |

## Inputs (read-only)

- `ShadowGoals` / `ShadowMentor`
- `ShadowTeaching`
- `ShadowKnowledge`
- `ShadowMemory`, Relationship, Session Learning, Watch sessions

## Output

`ExecutivePlan` containing `ExecutiveAgenda`, pending `ExecutiveDecision[]`, `ExecutiveRecommendation[]`, weekly snapshot.

## Non-goals

- No LLM training
- No autonomous execution
- No writes to goals, identity, or preferences
