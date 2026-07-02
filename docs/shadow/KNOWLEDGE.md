# Shadow — Knowledge Graph

Bounded context: `ShadowKnowledge`

API: `/api/shadow/knowledge/*`  
UI: `/settings/shadow/knowledge`

## Capabilities

- Knowledge graph built from Shadow Memory (S62) and Teaching (S63)
- Prerequisite reasoning and learning gap detection
- Learning path suggestions
- Prompt enrichment via `KnowledgeContextComposer`

## Storage

`storage/shadow/knowledge/{graphId}.json`

## Related architecture docs

- [Knowledge graph](architecture/KNOWLEDGE_GRAPH.md)
- [Schema](architecture/KNOWLEDGE_GRAPH_SCHEMA.md)
- [Reasoning engine](architecture/KNOWLEDGE_REASONING_ENGINE.md)
- [Paths](architecture/KNOWLEDGE_PATHS.md)
- [Learning gaps](architecture/LEARNING_GAPS.md)
