# Knowledge Graph Schema

```json
{
  "id": "uuid",
  "scopeKey": "default",
  "graphEnabled": true,
  "nodes": [
    {
      "key": "docker",
      "label": "Docker",
      "type": "technology",
      "explanation": "Container packaging and runtime.",
      "sources": ["memory", "teaching"]
    }
  ],
  "edges": [
    {
      "id": "hex",
      "fromKey": "docker",
      "toKey": "kubernetes",
      "type": "prerequisite",
      "label": "Docker → Kubernetes",
      "reason": "Kubernetes orchestrates Docker containers.",
      "source": "preset",
      "confidence": "high"
    }
  ],
  "masteries": [
    {
      "nodeKey": "docker",
      "percent": 45,
      "exposureCount": 3,
      "exerciseCount": 1,
      "explanationCount": 2,
      "videoIds": [],
      "confidence": "medium",
      "mastered": false
    }
  ]
}
```

Mastery threshold: `mastered = percent >= 80`.

Confidence mapping:

| Percent | Confidence |
|---------|------------|
| ≥ 80 | high |
| ≥ 40 | medium |
| < 40 | low |
