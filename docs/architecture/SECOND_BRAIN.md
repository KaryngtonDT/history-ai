# Second Brain

Sprint 67 bounded context. Planning doc — implement per `planning/Shadow/Sprint-67/TASK-0067.md`.

## Role

**Read-only aggregation layer** over S55–S66. Produces `KnowledgeWorkspace` for UI exploration — no new reasoning engines.

## Layering

| Layer | Package |
|-------|---------|
| Domain | `App\Domain\ShadowSecondBrain\` |
| Application | `App\Application\ShadowSecondBrain\` |
| Infrastructure | `App\Infrastructure\ShadowSecondBrain\` |
| HTTP | `App\Presentation\Http\Controller\ShadowSecondBrain\` |

Persistence: `storage/shadow/brain/{workspaceId}.json`

User overlays: bookmarks, notes, tags — separate collections in same aggregate or sibling store (document in TASK).

## Facade

`WorkspaceBuilder::syncWorkspace(scopeKey)`:

1. Read Memory, Knowledge graph, Teaching, Mentor, Executive, Goals (no writes)
2. `KnowledgeAggregator` → entries + sources
3. `DuplicateConceptResolver` + optional `KnowledgeMergeEngine`
4. Persist workspace
5. Return for API / search / diff

## Related docs

- [KNOWLEDGE_WORKSPACE.md](KNOWLEDGE_WORKSPACE.md)
- [KNOWLEDGE_DIFF.md](KNOWLEDGE_DIFF.md)
- [KNOWLEDGE_EXPLORER.md](KNOWLEDGE_EXPLORER.md)
- [TIMELINE_EXPLORER.md](TIMELINE_EXPLORER.md)
- [BOOKMARKS.md](BOOKMARKS.md)
- [../shadow/SECOND_BRAIN.md](../shadow/SECOND_BRAIN.md)
