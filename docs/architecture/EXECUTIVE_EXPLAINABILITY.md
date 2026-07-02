# Executive Explainability

Every executive decision must be understandable without trusting the model.

## Decision payload

```text
ExecutiveDecision
├── type, priority, status
├── ExecutiveReason (summary + detail)
├── evidence[] (source: knowledge | memory | teaching | mentor | session)
├── DecisionImpact[] (knowledge | goal | time | difficulty | confidence)
└── linkedGoalId?, linkedConceptKey?, linkedResourceId?
```

## Why? flow

1. UI `DecisionInspector` loads decision from dashboard or history
2. `DecisionExplanationBuilder` resolves evidence references to graph nodes, missions, or memory events
3. User sees chain: **Decision → Reason → Evidence → Goal**

## Watch mode

`ExecutiveContextComposer` adds one-line summaries for the highest-priority **pending** decision only — never auto-executes.

## Principles

- Deterministic rules produce reasons (no opaque scores)
- Same inputs → same decision draft
- Rejected/ignored decisions feed constraint flags for future planning
