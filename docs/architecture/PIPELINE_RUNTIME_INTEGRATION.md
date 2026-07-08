# Pipeline ↔ Runtime Integration

How the video **Processing Pipeline** consumes the **Runtime Kernel** after Sprint 71.1.

## Before Sprint 71.1

```text
Pipeline Settings UI
  → GET /api/ai/providers (AIEngineRegistry)
  → PUT /api/pipeline → PostgreSQL pipeline_configuration

Execution
  → AIProviderResolver
    → pipeline_configuration OR planner override
    → AIEngineRegistry match → factories

Runtime Dashboard
  → GET /api/runtime/dashboard (separate catalogue)
```

**Problem:** two registries, two ID namespaces, four notions of “current engine”.

## After Sprint 71.1

```text
Pipeline Settings UI
  → GET /api/runtime/capabilities/{stage}/selection-view
  → PUT /api/pipeline → PostgreSQL + RuntimeSelectionSynchronizer → configuration.json

Execution (RUNTIME_KERNEL_UNIFIED=true)
  → AIProviderResolver
    → EngineExecutionAdapter
      → RuntimeResolver
        → adapterKey → factories

Runtime Dashboard
  → GET /api/runtime/dashboard (same resolver semantics for “current”)
```

## Stage ↔ capability mapping

| Pipeline stage | Runtime capability |
| -------------- | ------------------ |
| `speech_to_text` | `speech_to_text` |
| `translation` | `translation` |
| `text_to_speech` | `text_to_speech` |
| `voice_clone` | `voice_clone` |
| `lip_sync` | `lip_sync` |
| `video_render` | `video_render` |

Mapper: `PipelineStageCapabilityMapper`.

Capabilities **without** pipeline stages: `ocr`, `vision`, `embeddings`, `reranking` — Runtime-only today.

## Dual-write (transition)

| Store | Role during transition |
| ----- | ---------------------- |
| `pipeline_configuration` (PostgreSQL) | Legacy API `GET/PUT /api/pipeline`; backward compatible |
| `configuration.json` | Runtime Selection Store — **SSOT target** |

On save: `SavePipelineConfigurationHandler` → `RuntimeSelectionSynchronizer`.

## Execution priority (per job)

`ResolvingPipelineConfiguration`:

1. `RuntimePipelineConfigurationContext` (planner/replay override)
2. PostgreSQL latest config
3. `AIEngineConfiguration` defaults

When kernel unified, provider ID inside config is still legacy-shaped; resolver normalizes at execution time via adapter.

## Planner integration

`DeterministicPipelinePlanner`:

- Still owns **content heuristics** (speakers, lighting, VRAM, strategy)
- Produces `preferredEngineId` in resolve context
- **Does not** own catalogue or readiness — delegates final pick to `RuntimeResolver`

## Frontend

| Component | Data source |
| --------- | ----------- |
| `PipelineBuilder` | `runtimeService.getCapabilitySelectionView(stage)` |
| `PipelineStageSelector` | Displays recommended/current/installed/blocked from selection-view |
| Dropdown values | Installed engines mapped to adapter keys |

`AIEngineSettings` page may still use `GET /api/ai/providers` until legacy removal.

## UI consistency rule

For each video capability, these fields must match between Dashboard card and Pipeline stage metadata:

- Reference, Recommended, Current, Installed, Blocked reason

**Implementation note:** Dashboard uses `RuntimeDashboardAssembler`; Pipeline uses `RuntimeResolver::capabilitySelectionView()`. Both should call the same resolver logic for “current” — drift between them is a bug.

## Feature flag

```bash
RUNTIME_KERNEL_UNIFIED=true   # default in services.yaml
RUNTIME_KERNEL_UNIFIED=false  # rollback to legacy resolver path
```

## Doctor

`scripts/doctor.sh` probes:

- `/api/runtime/readiness` (engine counts)
- `/api/runtime/capabilities/{capability}/selection-view` for each video stage

## Out of scope (Sprint 71.1)

- Worker Python migration
- Removal of `pipeline_configuration` table
- Runtime Settings UI for `PUT /api/runtime/selection`

## Related

- [RUNTIME_KERNEL.md](RUNTIME_KERNEL.md)
- [LEGACY_ENGINE_REGISTRY_DEPRECATION.md](LEGACY_ENGINE_REGISTRY_DEPRECATION.md)
