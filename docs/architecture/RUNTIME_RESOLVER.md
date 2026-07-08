# Runtime Resolver

The **Runtime Resolver** is the single decision point for engine selection in Lumen.

It answers:

> For this capability, this user, this hardware, and this context — which engine should run?

## Distinction: Current vs Resolved

| Concept | Meaning |
| ------- | ------- |
| **Current** (legacy) | Ambiguous — env var, DB row, or dashboard label |
| **Resolved** | Explicit decision with reason, confidence, executability, fallback |

## Request

`RuntimeResolveRequest`:

```json
{
  "capability": "speech_to_text",
  "context": {
    "language": "de",
    "durationSeconds": 4260,
    "hardwareProfile": "cpu_only",
    "preferredEngineId": "faster_whisper_large_v3",
    "consumer": "pipeline"
  }
}
```

### Context fields

| Field | Used by |
| ----- | ------- |
| `language` | Future model selection hints |
| `durationSeconds` | Planner / estimation |
| `preferredEngineId` | Planner context enrichment |
| `consumer` | Observability (`pipeline`, `shadow`, `doctor`, …) |

## Response

`EngineExecutionPlan` wrapping `ResolvedEngine`:

```json
{
  "planId": "a1b2c3…",
  "adapterKey": "faster_whisper",
  "parameters": {
    "executionProfile": "balanced",
    "language": "de",
    "durationSeconds": 4260
  },
  "resolvedEngine": {
    "engineId": "faster_whisper_large_v3",
    "displayName": "Faster Whisper Large V3",
    "capability": "speech_to_text",
    "adapterKey": "faster_whisper",
    "reason": "hardware_recommended",
    "confidence": 0.9,
    "executable": true,
    "blocked": false,
    "blockedReason": null,
    "provider": "docker",
    "executionProfile": "balanced",
    "fallback": null
  },
  "fallbackPlan": null
}
```

## Resolution policy (priority)

Applied in `RuntimeResolver::pickEngineId()`:

| Priority | Source | `RuntimeResolveReason` |
| -------- | ------ | ---------------------- |
| 1 | Manual selection in `configuration.json` | `user_selection` |
| 2 | Planner `preferredEngineId` in context | `planner_context` |
| 3 | Hardware `recommendedPipeline` | `hardware_recommended` |
| 4 | Profile `RecommendationEngine` | `profile_recommended` |
| 5 | Env-configured engine (`configured=true`) | `ops_bootstrap` |
| 6 | Catalogue default | `catalog_default` |
| 7 | Compatible fallback when blocked | `fallback` |

## Adapter mapping

Catalogue IDs differ from legacy provider keys. `EngineAdapterRegistry` maps:

| Catalogue ID | Adapter key |
| ------------ | ----------- |
| `faster_whisper_large_v3` | `faster_whisper` |
| `ollama_gemma3` | `ollama` |
| `openvoice_v2` | `openvoice` |
| `ffmpeg_nvenc` | `ffmpeg` |

Factories continue to use **adapter keys**; the resolver bridges catalogue semantics to execution.

## Selection view (UI contract)

`GET /api/runtime/capabilities/{capability}/selection-view` returns:

- `referenceEngineId` / `referenceDisplayName` — catalogue default
- `recommendedEngineId` / `recommendedDisplayName` — hardware + profile
- `currentEngineId` / `currentDisplayName` — **resolved** engine for this capability
- `installedEngineIds` — discovery-ready engines
- `blocked`, `blockedReason`, `executable`
- `resolvedEngine` — full decision payload

Pipeline Settings and Runtime Dashboard must consume the same semantics.

## Implementation

| Artifact | Path |
| -------- | ---- |
| Interface | `App\Application\Runtime\RuntimeResolverInterface` |
| Service | `App\Infrastructure\Runtime\Kernel\RuntimeResolver` |
| Adapter registry | `App\Infrastructure\Runtime\Kernel\EngineAdapterRegistry` |
| Stage mapping | `App\Application\Runtime\PipelineStageCapabilityMapper` |
| Controller | `RuntimeController::resolve()`, `capabilitySelectionView()` |

## Fallback behaviour

If the primary engine is blocked but a compatible alternative is ready:

- Resolver may select fallback with `reason: fallback` and reduced confidence
- If no executable engine exists: `blocked: true`, execution adapter throws

**No silent fallback** without logging reason and plan metadata.

## Consumers

| Consumer | Integration |
| -------- | ----------- |
| `EngineExecutionAdapter` | `planForStage()` |
| `AIProviderResolver` | When `RUNTIME_KERNEL_UNIFIED=true` |
| `DeterministicPipelinePlanner` | Context enrichment |
| `RuntimePlatformService` | API facade |
| `doctor.sh` | `selection-view` per video capability |
| `PipelineBuilder` (frontend) | `getCapabilitySelectionView()` |
