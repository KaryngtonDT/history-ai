# Shadow — Personal Mentor (Phase 6)

> Shadow understands **why** you learn — not just **what** you know.

## Shift

| Before | After |
|--------|-------|
| I watch a video → Shadow explains | I have a goal → Shadow chooses what, when, and in what order |
| Content-centric | Transformation-centric |
| Intelligent tutor | Personal mentor |

## Capabilities (Sprint 65)

- **Goals** — career, language, certification, personal, custom
- **Mentor plan** — roadmap, missions, milestones, skill radar
- **Learning missions** — goal-linked units with validation
- **Mentor conversation** — watch-time guidance tied to your destination
- **Weekly review** — progress bilan with user-approved plan changes
- **Multi-goal** — primary + secondary goals orchestrated by priority
- **Goal impact preview** — see how content advances each active goal

## Architecture

```text
Goals (ShadowGoals)
      │
      ▼
Mentor Engine (ShadowMentor)
      │
 ┌────┼─────────────┐
 ▼    ▼             ▼
Plans Missions Recommendations
      │
      ├──► Knowledge Graph (S64)
      ├──► Teaching Engine (S63)
      ├──► Memory (S62)
      └──► Relationship (S61)
      │
      ▼
ShadowWatchPromptBuilder
```

## Surfaces

| Surface | Route |
|---------|-------|
| Mentor dashboard | `/settings/shadow/mentor` |
| Goals (CRUD) | `/settings/shadow/mentor` (goals panel) |
| Watch companion | `/video/{id}/watch` — `ShadowMentorPanel` |

## Principles

- Deterministic and explainable — every recommendation has a reason
- User control — plan changes require approval
- No model training in Shadow bounded contexts
- Builds on S61–S64 — does not duplicate teaching or knowledge graphs

## API

See [API.md](API.md) — Mentor & Goals section (S65).

## Task

Implementation spec: `planning/Shadow/Sprint-65/TASK-0065.md`
