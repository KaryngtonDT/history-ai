# Knowledge Paths

`KnowledgePathFinder` exposes curated learning paths when enough nodes exist in the graph.

## Preset paths

| Path | Steps |
|------|-------|
| Container stack | docker → kubernetes → helm |
| Symfony architecture | dependency_injection → symfony_messenger → cqrs |
| GPU stack | gpu → cuda |

## API

`GET /api/shadow/knowledge/path` returns:

```json
{
  "scopeKey": "default",
  "paths": [
    {
      "key": "docker_to_kubernetes",
      "label": "Docker → Kubernetes",
      "steps": [
        { "key": "docker", "label": "Docker" },
        { "key": "kubernetes", "label": "Kubernetes" }
      ]
    }
  ]
}
```

Paths are included in the full graph payload (`paths` field) from `GET /api/shadow/knowledge/graph`.

## Path walking

`findPath(fromKey, toKey)` walks prerequisite edges for ad-hoc route discovery.
