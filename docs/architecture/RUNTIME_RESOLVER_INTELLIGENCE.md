# Runtime Resolver Intelligence

Sprint 72 upgrades the **Runtime Resolver** from a pure selection function into an **intelligent recommendation engine** that explains every decision.

## Position in the kernel

```text
RuntimeResolveRequest (capability + context)
        │
RuntimeResolver::resolveEngineMetadata()
        │
pickEngineId() ──► ResolvedEngine (id, reason, confidence)
        │
RuntimeResolverIntelligence::enrich()
        │
ResolvedEngineIntelligence (alternatives, estimates, explanation)
        │
EngineExecutionPlan
```

## Decision inputs

| Input | Source |
| ----- | ------ |
| Hardware profile | `HardwareRepository`, `HardwareReportBuilder` |
| Capability | `EngineCatalogCapability` |
| Media duration / language | `RuntimeResolveContext` |
| User preferences | `RuntimeConfiguration` (profile, modes, selections) |
| Hardware recommendation | `recommendedPipeline()` per stage key |
| Profile recommendation | `RecommendationEngine` |
| Planner override | `context.preferredEngineId` |
| Historical benchmarks | `EngineStatisticsAggregator` |
| Compatibility | `RuntimeCompatibilityService` |

## Selection modes

Per-capability `CapabilitySelectionMode` gates automatic switching:

| Mode | Behavior |
| ---- | -------- |
| **Auto** | Full policy chain (planner → hardware → profile → ops → default) |
| **Manual** | `manualSelections[capability]` wins |
| **Locked** | `lockedSelections[capability]` — no fallback override |

Locked mode skips automatic fallback when primary is blocked.

## Decision outputs

`ResolvedEngine` fields:

| Field | Description |
| ----- | ----------- |
| `engineId` / `displayName` | Chosen engine |
| `reason` | `RuntimeResolveReason` enum |
| `confidence` | 0.0–1.0 |
| `executable` / `blocked` | Readiness gate |
| `fallback` | `RuntimeFallbackPlan` when alternative exists |
| `intelligence` | `ResolvedEngineIntelligence` |

`ResolvedEngineIntelligence` fields:

| Field | Description |
| ----- | ----------- |
| `alternativeEngineId` | Second-best from analytics or ready candidates |
| `estimatedDurationSeconds` | Median from history or context estimate |
| `expectedAccuracy` | Historical success rate |
| `explanation` | Human-readable rationale |

## Example

```text
Capability: speech_to_text
Hardware:   AMD laptop
Duration:   72 minutes
Mode:       auto

→ Faster Whisper Large V3
  Reason:     hardware_recommended
  Confidence: 0.90
  Alternative: whisper_cpp
  Explanation: "Hardware profile recommends faster_whisper_large_v3 for speech on this CPU."
```

## API

`POST /api/runtime/resolve`

```json
{
  "capability": "speech_to_text",
  "context": {
    "language": "fr",
    "durationSeconds": 4320,
    "preferredEngineId": null
  }
}
```

Response includes `resolvedEngine.intelligence` and full `EngineExecutionPlan`.

## Shadow usage

Shadow reads `selection.resolved[].reason` and `intelligence.explanation` via `runtimeContext` — see [RUNTIME_ENGINE_MANAGEMENT.md](RUNTIME_ENGINE_MANAGEMENT.md) Shadow section.

## Related

- [RUNTIME_RESOLVER.md](RUNTIME_RESOLVER.md) — policy ordering
- [RUNTIME_RECOMMENDATIONS.md](RUNTIME_RECOMMENDATIONS.md) — profile-level picks
- [RUNTIME_ANALYTICS.md](RUNTIME_ANALYTICS.md) — execution history feeding estimates
