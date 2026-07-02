# Shadow — Executive Function (Phase 6 closure)

> Shadow does not only answer — it **pilots the learning journey** (with your approval).

## Shift

| Before (S65) | After (S66) |
|--------------|-------------|
| Mentor explains and assigns missions | Executive orchestrates *when* and *what* happens next |
| Goal-directed guidance | Full path orchestration |
| Recommendations | **Decisions** (approve / skip / defer) |

```text
Goal → Executive Function → Mentor → Teaching → Conversation
```

## Capabilities (Sprint 66)

- **Executive plan** — agenda, priorities, pending decisions
- **Learning agenda** — today, upcoming, recommendations
- **Decision engine** — review, learn, skip, pause, accelerate, resource recommendations
- **Explainability** — every decision: reason, evidence, goal link, **Why?**
- **Executive watch** — objective, priority, pause/review hints on watch
- **Weekly executive review** — progress report in dashboard (never auto-sent)
- **Energy-aware planning** *(stretch)* — respects declared available time
- **Opportunity engine** *(stretch)* — turns activity patterns into recommendations

## Hard constraints

The executive engine **never**:

- modifies profile or preferences ;
- deletes data or changes goals ;
- acts without user validation.

It **proposes** only.

## Architecture

```text
ShadowGoals (S65)
      │
      ▼
ShadowKnowledge (S64)
      │
      ▼
ShadowExecutive (S66)
      │
 ┌────┼─────────────┐
 ▼    ▼             ▼
Agenda Decisions Recommendations
      │
      ├──► ShadowMentor (S65)
      ├──► ShadowTeaching (S63)
      ├──► ShadowMemory (S62)
      └──► Relationship + Adaptive Learning
      │
      ▼
ShadowWatchPromptBuilder
```

## Surfaces

| Surface | Route |
|---------|-------|
| Executive dashboard | `/settings/shadow/executive` |
| Watch executive bar | `/video/{id}/watch` — `ExecutiveWatchBar` |

## Post-S66: Shadow intelligence stack is **feature-complete for v1**. Phase 7 makes it **visible and daily-useful** — starting with Second Brain (S67).

See [SECOND_BRAIN.md](SECOND_BRAIN.md) and [ROADMAP.md](ROADMAP.md).

## API

See [API.md](API.md) — Executive section (S66).

## Task

Implementation spec: `planning/Shadow/Sprint-66/TASK-0066.md`
