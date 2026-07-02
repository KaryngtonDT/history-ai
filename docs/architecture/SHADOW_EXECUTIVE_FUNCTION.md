# Shadow Executive Function

Sprint 66 bounded context. **Planning document** — implement per `planning/Shadow/Sprint-66/TASK-0066.md`.

## Role

Sits between **Knowledge Graph + Goals** and **Mentor/Teaching**. Produces an `ExecutivePlan`: agenda, prioritized decisions, and recommendations. Does not mutate user data or goals.

## Layering

| Layer | Package |
|-------|---------|
| Domain | `App\Domain\ShadowExecutive\` |
| Application | `App\Application\ShadowExecutive\` |
| Infrastructure | `App\Infrastructure\ShadowExecutive\` |
| HTTP | `App\Presentation\Http\Controller\ShadowExecutive\` |

Persistence: `storage/shadow/executive/{planId}.json`

## Facade

`ExecutiveCoordinator::syncPlan(scopeKey)`:

1. Load goals + mentor + teaching + knowledge (read-only inputs)
2. Run `ExecutivePlanner` + detectors
3. Persist `ExecutivePlan`
4. Return plan for API and prompt composition

## Prompt integration

`ExecutiveContextComposer` injects pending decisions and today's agenda into `ShadowWatchPromptBuilder` **before** mentor context.

## Related docs

- [EXECUTIVE_ENGINE.md](EXECUTIVE_ENGINE.md)
- [LEARNING_AGENDA.md](LEARNING_AGENDA.md)
- [DECISION_ENGINE.md](DECISION_ENGINE.md)
- [EXECUTIVE_EXPLAINABILITY.md](EXECUTIVE_EXPLAINABILITY.md)
- [../shadow/EXECUTIVE.md](../shadow/EXECUTIVE.md)
