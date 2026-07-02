# Shadow — Second Brain (Phase 7)

> Shadow doesn't only remember — it gives you a **navigable personal encyclopedia**.

## Shift

| Before (S66) | After (S67) |
|--------------|-------------|
| Intelligence spread across 8+ bounded contexts | **One workspace** to explore everything Shadow knows |
| Shadow answers questions | Shadow **navigates** your knowledge |
| Hidden graph + memory | Visible tree, search, timeline, sources |

Type **Docker** → instant overview:

```text
Docker
├── 8 videos
├── 3 PDFs
├── 17 conversations
├── 12 exercises
├── 4 missions
├── 2 frequent errors
├── Kubernetes (related)
├── Containers
└── Networking
```

## Capabilities (Sprint 67)

- **Knowledge Workspace** — unified entries from Memory, Graph, Teaching, Mentor, Executive, Goals
- **Knowledge Explorer** — tree + graph navigation at `/settings/shadow/brain`
- **Knowledge Diff** — before watch: "12 new concepts" or "85% already known"
- **Knowledge Merge** — same concept across PDFs/sources fused deterministically
- **Knowledge Sources** — jump to exact video timestamp, PDF page, conversation, mission
- **Timeline Explorer** — learning history by month
- **Bookmarks & notes** — user-owned overlays (never mixed with auto-knowledge)
- **Concept Evolution** *(stretch)* — mastery history per concept
- **Connections Heatmap** *(stretch)* — strongest knowledge domains

## Principles

- **Aggregation, not new AI** — read-only inputs from existing Shadow contexts
- Deterministic diff/merge — every insight explainable
- User notes/bookmarks are **separate** from generated knowledge
- No model training

## Architecture

```text
Sources → Memory → Knowledge Graph
                        │
                        ▼
              ShadowSecondBrain (S67)
                        │
        ┌───────────────┼───────────────┐
        ▼               ▼               ▼
    Explorer         Search          Diff / Merge
                        │
                        ▼
              /settings/shadow/brain
```

## Surfaces

| Surface | Route |
|---------|-------|
| Second Brain | `/settings/shadow/brain` |
| Knowledge Diff | watch / import preview (panel) |

## Post-S67 product arc

S68 Desktop Companion → S69 Coding Companion → S70 Life Dashboard → S71 Active Recall → S72 Delight → S73+ Chronicle.

Public API / billing / multi-tenant **intentionally deferred**.

## Task

`planning/Shadow/Sprint-67/TASK-0067.md`
