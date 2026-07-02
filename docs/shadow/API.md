# Shadow — API

Base prefix: `/api/shadow/`

## Knowledge (S64)

```text
GET  /api/shadow/knowledge/graph
GET  /api/shadow/knowledge/node/{id}
GET  /api/shadow/knowledge/path
GET  /api/shadow/knowledge/gaps
GET  /api/shadow/knowledge/related
POST /api/shadow/knowledge/search
POST /api/shadow/knowledge/rebuild
POST /api/shadow/knowledge/reset
```

See [KNOWLEDGE.md](KNOWLEDGE.md).

## Teaching (S63)

```text
GET  /api/shadow/teaching/path
GET  /api/shadow/teaching/current
GET  /api/shadow/teaching/objectives
GET  /api/shadow/teaching/revisions
GET  /api/shadow/teaching/exercises
POST /api/shadow/teaching/exercise/{id}/answer
POST /api/shadow/teaching/checkpoint/{id}/complete
PUT  /api/shadow/teaching/preferences
POST /api/shadow/teaching/reset
```

Optional query/body: `scopeKey` (default `default`).

## Memory (S62)

See [MEMORY.md](MEMORY.md).

## Relationship (S61)

See [RELATIONSHIP.md](RELATIONSHIP.md).

OpenAPI: `/api/docs` (Nelmio auto-discovery from Symfony routes).
