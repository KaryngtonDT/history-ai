# Runtime Kernel

Lumen uses a single **Runtime Kernel** as the decision layer for AI engines.

## Principle

```text
Runtime decides.
Pipeline orchestrates.
Worker executes.
UI observes.
```

Consumers (Pipeline, Shadow, Mobile, Browser, Doctor, future Agents) must not embed engine catalogues or provider lists. They declare a **capability** and optional **context**; the kernel returns a **resolved engine** and an **execution plan**.

## Architecture

```text
Lumen Runtime Kernel
        │
   Capability Registry (public)
        │
   Engine Catalog (internal)
        │
   Discovery + Readiness
        │
   Hardware Profile + Compatibility
        │
   Selection Store (user preferences)
        │
   Recommendation Engine
        │
   Runtime Resolver  ◄── decision point
        │
   Engine Execution Plan
        │
   Execution Adapter → Provider Factories → Binaries
```

## Layers

| Layer | Location | Responsibility |
| ----- | -------- | -------------- |
| Engine Catalog | `EngineCatalogDefinitions` | Static definitions (~33 engines, 10 capabilities) |
| Discovery | `EngineReadinessAssessor`, scanners | Live installed/ready state |
| Selection Store | `FileRuntimeRepository` → `configuration.json` | Profile, selectionMode, manualSelections |
| Hardware | `HardwareDetector`, `HardwareReportBuilder` | Profile classification + pipeline recommendations |
| Compatibility | `RuntimeCompatibilityService` | Hardware vs requirement matrix |
| Recommendations | `RecommendationEngine` | Profile-based engine picks |
| **Runtime Resolver** | `RuntimeResolver` | `capability + context → ResolvedEngine` |
| Execution Adapter | `EngineExecutionAdapter` | Plan → legacy provider key for factories |
| Provider factories | `SpeechToTextProviderFactory`, etc. | Instanciation technique (unchanged) |

## Capability Registry (direction)

Today the kernel exposes **10 AI capabilities** (`EngineCatalogCapability`). The long-term model:

```text
Capability Registry  →  what the platform can do (speech_to_text, ocr, vision, …)
Engine Registry      →  how each capability is implemented (engines)
```

Future capabilities (Summarization, Reasoning, Browser, Filesystem, …) register in the Capability Registry without creating parallel registries per product surface.

## API surface

| Method | Path | Purpose |
| ------ | ---- | ------- |
| POST | `/api/runtime/resolve` | Resolve capability → execution plan |
| GET | `/api/runtime/selection` | Selection store + resolved video capabilities |
| PUT | `/api/runtime/selection` | Update profile / manual selections |
| GET | `/api/runtime/capabilities` | All capabilities |
| GET | `/api/runtime/capabilities/{capability}/selection-view` | Unified UI payload |
| GET | `/api/runtime/dashboard` | Aggregated health dashboard |
| GET | `/api/runtime/readiness` | Discovery snapshot |

## Feature flag

`RUNTIME_KERNEL_UNIFIED` (default `true` via `services.yaml`):

- `true` — `AIProviderResolver` resolves via `EngineExecutionAdapter` → `RuntimeResolver`
- `false` — legacy path: `pipeline_configuration` + `AIEngineRegistry`

## Related documents

- [RUNTIME_RESOLVER.md](RUNTIME_RESOLVER.md)
- [ENGINE_EXECUTION_PLAN.md](ENGINE_EXECUTION_PLAN.md)
- [PIPELINE_RUNTIME_INTEGRATION.md](PIPELINE_RUNTIME_INTEGRATION.md)
- [LEGACY_ENGINE_REGISTRY_DEPRECATION.md](LEGACY_ENGINE_REGISTRY_DEPRECATION.md)
- [CAPABILITY_PLATFORM_VISION.md](CAPABILITY_PLATFORM_VISION.md)
