# Knowledge Recall Engine

Deterministic recall layer for Shadow Memory (Sprint 62).

## Inputs

- `MemoryTimeline` — durable knowledge items and connections
- Viewer question text

## Steps

1. `KnowledgeSimilarityResolver` extracts concept keys from the question (alias map).
2. `KnowledgeRecallEngine` looks up primary concept progress and optional prerequisites.
3. `KnowledgeConnectionBuilder` adds related concept lines (max 2).
4. `MemoryContextComposer` returns prompt lines when memory is enabled.

## Output example

```text
Before explaining Symfony Messenger, recall that the learner already studied Dependency Injection (45% progress).
Prior knowledge for Symfony Messenger: progress 12%, seen in 1 videos.
Knowledge connection: Docker → Kubernetes.
Use prior learning naturally; do not repeat full previous explanations unless needed.
```

## Design constraints

- No LLM inference for recall selection
- Empty recall when no concepts match
- Respects `memoryEnabled` on timeline
