# Runtime Recommendations

Runtime continuously computes **which engine fits best** for each capability — by hardware, profile, and execution history.

## Services

| Service | Role |
| ------- | ---- |
| `RecommendationEngine` | Profile-driven picks per capability |
| `RuntimeRecommendationProfilesService` | Multi-profile comparison matrix |
| `HardwareReportBuilder` | Hardware-class pipeline recommendations |

## Recommendation inputs

- Detected hardware profile (CPU/GPU/RAM class)
- User profile (`EngineProfileName`: balanced, quality, fast, …)
- Compatibility matrix (`RuntimeCompatibilityService`)
- Execution analytics (`EngineStatisticsAggregator`)
- Catalog defaults (`EngineCatalogDefinitions`)

## API

| Method | Path | Purpose |
| ------ | ---- | ------- |
| GET | `/api/runtime/recommendations` | Per-capability recommendations |
| GET | `/api/runtime/recommendations/profiles` | Multi-profile comparison |

## Profile matrix

`RuntimeRecommendationProfilesService::profiles()` returns:

| Profile key | Label | Basis |
| ----------- | ----- | ----- |
| `bestQuality` | Best Quality | `RecommendationEngine` quality bias |
| `fastest` | Fastest | Analytics median duration per capability |
| `lowestRam` | Lowest RAM | Profile balanced / lightweight picks |
| `hardwareRecommended` | Hardware Recommended | `HardwareReportBuilder::recommendedPipeline()` |
| `currentSelection` | Current Selection | Active resolver choices |

Each profile contains `items[]` with `capability`, `engineId`, `displayName`, `reason`.

## Hardware pipeline map

Video capabilities map to hardware pipeline keys:

```text
speech       → Speech-to-Text
translation  → Translation
tts          → Text-to-Speech
voiceClone   → Voice Clone
lipSync      → Lip Sync
render       → Video Render
```

## Example output

```text
Hardware: AMD Laptop

Speech-to-Text   → faster_whisper_large_v3
Translation      → gemma (profile)
TTS              → f5_tts
Voice Clone      → openvoice
Lip Sync         → wav2lip
Video Render     → ffmpeg_av1
```

## Consumers

| Consumer | Usage |
| -------- | ----- |
| `RuntimeResolver` | `profileRecommended` in pickEngineId |
| `RuntimeEngineManagementAssembler` | `recommendations` in management payload |
| `RuntimeDoctorReportService` | recommendation summary in doctor report |
| `RuntimeShadowContextBuilder` | `recommendationProfiles` for Shadow Q&A |
| Provision Center | recommended badge on engine cards |

## Shadow questions

`runtimeContext.promptHints` maps user questions to data sources:

- "Which engine should I use?" → `recommendationProfiles`
- "Which engine performs best?" → `profiles.fastest`
- "Why did Runtime choose this?" → `selection.resolved[].reason` + `intelligence.explanation`

## Related

- [RUNTIME_RESOLVER_INTELLIGENCE.md](RUNTIME_RESOLVER_INTELLIGENCE.md)
- [RUNTIME_ANALYTICS.md](RUNTIME_ANALYTICS.md)
- [RUNTIME_ENGINE_SELECTION.md](RUNTIME_ENGINE_SELECTION.md)
