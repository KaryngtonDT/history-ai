# Knowledge Workspace

Unified view model for Sprint 67.

## Aggregate

`KnowledgeWorkspace` contains:

- `KnowledgeCollection` — auto-generated entries (concept key, summary, mastery, sources, edges)
- Bookmarks, personal notes, tags (user-owned)
- Statistics snapshot (counts by source type, domain heatmap data)
- Last synced at

## Sync

Triggered by `WorkspaceBuilder::syncWorkspace()` — not on every HTTP request (cache + explicit rebuild).

Rebuild API: `POST /api/shadow/brain/rebuild`

## Non-goals

- Does not replace `ShadowKnowledge` graph storage
- Does not mutate Memory, Teaching, or Goals
