# Knowledge Reasoning Engine

`ReasoningEngine` resolves concepts from viewer questions and produces prompt lines for Shadow answers.

## Pipeline

1. `GraphConceptResolver` — extract concept keys from question text (alias map)
2. `PrerequisiteChecker` — evaluate mastered prerequisites (graph edges + fallback map)
3. `LearningGapDetector` — list missing prerequisites
4. `ReasoningExplanationBuilder` — human-readable prompt lines

## Output

```php
[
    'primaryKey' => 'kubernetes',
    'primaryLabel' => 'Kubernetes',
    'readinessPercent' => 0,
    'promptLines' => ['Still missing: □ Docker.', ...],
    'gaps' => [...],
]
```

## Integration

- `KnowledgeContextComposer` → `ShadowWatchPromptBuilder`
- `KnowledgeBuilder::recordQuestion` syncs graph after each Shadow question

When `graphEnabled` is false, reasoning returns empty prompt lines.
