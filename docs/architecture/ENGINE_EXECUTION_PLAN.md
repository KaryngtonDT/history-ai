# Engine Execution Plan

An **Engine Execution Plan** is the contract between the Runtime Kernel and execution layers (Symfony handlers, future Worker integrations).

It materializes a **ResolvedEngine** decision into something executors can act on.

## Structure

| Field | Type | Purpose |
| ----- | ---- | ------- |
| `planId` | string | Traceability; correlates with `EngineExecutionHistory` (Sprint 70.9) |
| `resolvedEngine` | `ResolvedEngine` | Full decision metadata |
| `adapterKey` | string | Legacy factory key (`faster_whisper`, `ollama`, …) |
| `parameters` | object | Execution hints: profile, language, duration |
| `fallbackPlan` | `RuntimeFallbackPlan?` | Alternate engine if primary fails |

## ResolvedEngine

| Field | Purpose |
| ----- | ------- |
| `engineId` | Catalogue ID (`faster_whisper_large_v3`) |
| `displayName` | Human label |
| `capability` | `EngineCatalogCapability` |
| `adapterKey` | Factory routing key |
| `reason` | `RuntimeResolveReason` enum |
| `confidence` | 0.0–1.0 |
| `executable` | Discovery says ready |
| `blocked` | Cannot run on current hardware/install state |
| `blockedReason` | Human-readable explanation |
| `provider` | `docker`, `host`, etc. |
| `executionProfile` | User profile (`balanced`, `fast`, …) |
| `fallback` | Optional nested fallback plan |

## Flow

```text
PipelineOrchestrator / ProcessVideoHandler
  → AIProviderResolver::resolveSpeechToText() [etc.]
    → EngineExecutionAdapter::legacyProviderIdForStage()
      → RuntimeResolver::resolveCapability()
        → EngineExecutionPlan
          → SpeechToTextProviderFactory::create()
            → faster-whisper binary
```

## Legacy path (flag off)

When `RUNTIME_KERNEL_UNIFIED=false`:

```text
AIProviderResolver
  → ResolvingPipelineConfiguration (PostgreSQL)
    → AIEngineRegistry validation
      → Provider factory
```

## No silent fallback

Rules:

1. If `resolvedEngine.blocked && !resolvedEngine.executable` and no ready fallback → **throw** with `blockedReason`
2. Automatic fallback only when `RuntimeResolver` explicitly sets `reason: fallback` on a ready engine
3. `planId` should be recorded in `EngineExecutionHistory` when stage completes (Sprint 70.9 integration target)

## Example

Input context:

```json
{
  "capability": "lip_sync",
  "context": { "hardwareProfile": "cpu_only", "consumer": "pipeline" }
}
```

Plan output (illustrative):

```json
{
  "planId": "…",
  "adapterKey": "wav2lip",
  "resolvedEngine": {
    "engineId": "wav2lip",
    "reason": "hardware_recommended",
    "confidence": 0.9,
    "executable": true,
    "blocked": false
  }
}
```

## Related

- [RUNTIME_RESOLVER.md](RUNTIME_RESOLVER.md)
- [ENGINE_EXECUTION_HISTORY.md](ENGINE_EXECUTION_HISTORY.md)
- [PIPELINE_RUNTIME_INTEGRATION.md](PIPELINE_RUNTIME_INTEGRATION.md)
