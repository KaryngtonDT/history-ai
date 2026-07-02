# Relationship Profile

`RelationshipProfile` is the aggregate root for Sprint 61.

## Contents

- `RelationshipTraitCollection` — interests, habits, motivators, communication styles
- `RelationshipSignalCollection` — raw incoming events
- `RelationshipObservationCollection` — explainable timeline entries
- `SharedReferenceCollection` — reusable session references for prompts
- `RelationshipPendingChangeCollection` — approval queue
- `RelationshipPreferences` — adaptive toggles and approval policy

## Trait shape

Each `RelationshipTrait` includes:

- `type`, `key`, `label`
- `strength` (`low` → `very_high`)
- `confirmed` vs inferred
- `enabled` for manual disable
- `explanation` for UI and audit

## Score

`relationshipScore()` is a transparent function of enabled trait strengths and confirmation bonus — not an opaque ML score.
