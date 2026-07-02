# Knowledge Explorer

UI + API for browsing the Second Brain tree and graph.

## Route

`/settings/shadow/brain` — `SecondBrainCenter`

## Layout

Four zones: toolbar (search, filters, timeline, stats) · explorer tree · concept detail · bottom strip (notes, bookmarks, insights, revision queue).

## API

- `GET /api/shadow/brain` — dashboard payload
- `GET /api/shadow/brain/concepts` — tree/list
- `GET /api/shadow/brain/concept/{id}` — detail + sources + related
- `GET /api/shadow/brain/search?q=…`

## Frontend

`frontend/src/features/shadowBrain/KnowledgeExplorer/`, `ConceptDetail/`, `KnowledgeSearch/`
