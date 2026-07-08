# Runtime Capability Classification

Runtime classifies every capability so health, validation, and recommendations treat core pipeline work differently from optional extensions and premium hardware features.

## Classifications

| Value | Meaning |
|-------|---------|
| `core` | Required for the primary video pipeline |
| `optional` | Catalogued extensions, not installed by default |
| `premium` | Hardware-dependent premium features |
| `experimental` | Disabled or in-progress capabilities |
| `deprecated` | Retained for visibility only |

## Metadata per capability

Each capability exposes:

- `classification`
- `required`
- `enabledByDefault`
- `hardwareDependent`
- `installable`
- `recommended`

## Default mapping

| Capability | Classification |
|------------|----------------|
| speech_to_text | CORE |
| translation | CORE |
| text_to_speech | CORE |
| voice_clone | CORE |
| video_render | CORE |
| lip_sync | PREMIUM |
| ocr | OPTIONAL |
| vision | OPTIONAL |
| embeddings | OPTIONAL |
| reranking | OPTIONAL |

## Source of truth

`RuntimeCapabilityClassificationRegistry` in the backend is the single registry. Dashboard, Doctor, validation, Shadow, and UI all consume the same evaluated platform health payload.
