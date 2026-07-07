# Background Pipeline Orchestration

Persistent pipeline jobs decouple media processing from the UI.

## Principles

- One active job per source and stage
- Page refresh reads existing state; never creates duplicate jobs
- Background workers continue independently
- Completed stages require explicit user confirmation before continuing
- Restarting an earlier stage invalidates dependent stages (artifacts marked stale, not deleted)

## Core components

| Component | Responsibility |
|-----------|----------------|
| `PipelineJob` | Persistent aggregate for stage state and progress |
| `PipelineOrchestrator` | Start, complete, fail, continue, choice flows |
| `PipelineDependencyResolver` | Invalidation and next-stage mapping |
| `PipelineProgressService` | Progress updates and heartbeat |
| `PipelineNotificationService` | User-visible notifications |

## API

```
GET  /api/pipeline/jobs/{sourceId}
GET  /api/pipeline/jobs/{sourceId}/events
GET  /api/pipeline/jobs/{sourceId}/{stage}
POST /api/pipeline/jobs/{sourceId}/{stage}/start
POST /api/pipeline/jobs/{sourceId}/{stage}/cancel
POST /api/pipeline/jobs/{sourceId}/{stage}/continue
POST /api/pipeline/jobs/{sourceId}/{stage}/choice
```

## Storage

- `pipeline_job` — job state
- `pipeline_notification` — notifications
- `video_transcript.metadata` — transcript source metadata
