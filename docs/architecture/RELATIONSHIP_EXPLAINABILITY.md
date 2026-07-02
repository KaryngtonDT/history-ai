# Relationship Explainability

Every durable relationship change must be explainable in the UI.

## Sources

| Source | Meaning |
|--------|---------|
| `user` | Explicit utterance or settings edit |
| `signal` | Deterministic detector output |
| `system` | Bootstrap / reset |

## Portrait layers

1. **Confirmed** — explicit or approved traits
2. **Hypotheses** — inferred traits awaiting approval or display-only
3. **Questions** — optional prompts to improve personalization

## Approval flow

Inferred traits with `requireApprovalForInferences=true` create `RelationshipPendingChange` entries instead of silently updating the profile.

Users approve or reject via:

- Settings UI buttons
- `POST /api/shadow/relationship/configure` with `confirmed: true`

No silent learning.
