# Learning Gaps

`LearningGapDetector` identifies prerequisites that block progress toward a goal concept.

## Detection

For each prerequisite of the target node:

- If mastery `< 80%`, the concept is a gap
- Includes recommended review text and underlying reason from the edge

## Radar API

`GET /api/shadow/knowledge/gaps?goalKey=kubernetes`

```json
{
  "scopeKey": "default",
  "radar": {
    "goalKey": "kubernetes",
    "goalLabel": "Kubernetes",
    "readinessPercent": 0,
    "gaps": [
      {
        "conceptKey": "docker",
        "label": "Docker",
        "masteryPercent": 45,
        "missing": true,
        "recommended": "Review Docker before continuing.",
        "reason": "Kubernetes orchestrates Docker containers."
      }
    ]
  }
}
```

## Fallback prerequisites

When no graph edges exist, `PrerequisiteChecker` uses a static fallback map (e.g. kubernetes → docker, cuda → gpu + parallelism + threads).
