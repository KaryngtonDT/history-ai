# Sprint 61 Verification — Shadow Relationship Engine

## Backend

- [x] `Domain/ShadowRelationship` aggregate, traits, signals, observations, shared references, pending changes
- [x] Deterministic detectors (interest, habit, motivation, conversation style)
- [x] `RelationshipEvolutionEngine` with approval queue
- [x] `RelationshipContextComposer` wired into `ShadowWatchPromptBuilder`
- [x] Ask flow records relationship signals on questions
- [x] File persistence `storage/shadow/relationship`
- [x] API controllers under `/api/shadow/relationship/*`
- [x] PHPUnit: `InterestDetectorTest`, `RelationshipUtteranceInterpreterTest`

## Frontend

- [x] `services/shadowRelationship/*` repository pattern
- [x] `ShadowRelationshipCenter` with Portrait, interests, habits, timeline, teach + approve
- [x] Route `/settings/shadow/relationship` with Identity tab
- [x] i18n EN / FR / DE

## Manual checks

```bash
make prod-rebuild && make migrate && make doctor
```

1. Open `/settings/shadow/relationship`
2. Verify portrait score, confirmed vs hypotheses
3. Teach: `Shadow, remember that I like football analogies.` → pending change → Apply
4. Ask a question on `/video/{id}/watch` and confirm prompt enrichment via shared references over time

## Out of scope (by design)

- No worker / pipeline changes
- No model training
- Sprint 60 session learning domain unchanged
