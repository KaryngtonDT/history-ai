# Decision Engine

User-validated proposals from Shadow Executive (Sprint 66).

## Decision lifecycle

```text
Pending → Approved | Rejected | Deferred | Ignored
```

## Types

`review`, `learn`, `skip`, `pause`, `accelerate`, `slow_down`, `recommend_video`, `recommend_pdf`, `recommend_audio`, `recommend_exercise`, `recommend_mission`, `recommend_revision`

## User actions

| Action | API |
|--------|-----|
| Approve | `POST .../decision/{id}/approve` |
| Reject | `POST .../decision/{id}/reject` |
| Defer | `POST .../decision/{id}/defer` |
| Never suggest again | Stored on decision as `ignored` + constraint flag |

## Example flow

```text
Goal: Learn Kubernetes
Graph: docker mastery stale (28 days)
→ Decision: Review Docker (priority: high)
→ Reason: Kubernetes depends on Docker
→ User: Approve → agenda updated, mentor/teaching sync read-only
```

Approved decisions may trigger **read-only** replan via `ExecutiveCoordinator::syncPlan` — they do not mutate goals or preferences.

## Constraints

See [EXECUTIVE_EXPLAINABILITY.md](EXECUTIVE_EXPLAINABILITY.md) for reason/evidence model.
