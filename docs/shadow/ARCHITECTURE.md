# Shadow — Architecture

```text
Watch / PDF / Video artefacts
        │
        ▼
Shadow Memory (S62) ──► Knowledge recall
Shadow Relationship (S61) ──► Traits & rapport
Session Learning (S60) ──► In-session adaptation
Shadow Identity (S58) ──► Voice & persona
        │
        ▼
Shadow Teaching (S63) ──► Paths, exercises, revisions, checkpoints
Shadow Knowledge (S64) ──► Graph, prerequisites, gaps, reasoning
Shadow Mentor (S65) ──► Goals, roadmap, missions, weekly review
        │
        ▼
ShadowWatchPromptBuilder ──► Chat provider
```

Bounded contexts live under `backend/src/Domain/Shadow*`.

Persistence: `storage/shadow/{identity,sessions,relationship,memory,teaching,knowledge,goals,mentor}/`.

See also `docs/architecture/SHADOW_TEACHING_ENGINE.md`.
