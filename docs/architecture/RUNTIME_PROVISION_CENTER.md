# Runtime Provision Center

The **Provision Center** is Lumen's central **AI Engine Manager** UI — one page for every capability and every catalogued engine.

## Route

`/settings/runtime/engines`

| Layer | Path |
| ----- | ---- |
| Page | `frontend/src/pages/RuntimeEngines/RuntimeEnginesPage.tsx` |
| Feature | `frontend/src/features/runtime/RuntimeProvisionCenter/` |
| API | `GET /api/runtime/engines/management` |
| Assembler | `RuntimeEngineManagementAssembler` |

## Page structure

```text
AI Engine Manager
├── Principle banner ("Runtime decides. Worker executes. UI observes.")
└── For each capability (10)
    ├── Header: label, current, recommended
    ├── Mode switch: Auto | Manual | Locked
    ├── Manual/Locked: radio engine picker
    └── Engine grid
        └── EngineCard × N
```

## Capability coverage

All `EngineCatalogCapability` values:

| Capability | Video pipeline |
| ---------- | -------------- |
| Speech-to-Text | yes |
| Translation | yes |
| Text-to-Speech | yes |
| Voice Clone | yes |
| Lip Sync | yes |
| Video Render | yes |
| Vision | no |
| OCR | no |
| Embeddings | no |
| Reranking | no |

## Engine card

**Status badges:** Current, Recommended, Reference, Ready, Blocked, Mock

**Metrics (when available):**

- Provider, benchmark score (`relativeSpeedLabel`)
- Average duration, success rate %
- Blocked reason (compatibility or readiness)
- Version, model info, dependencies

**Actions:**

| Action | API |
| ------ | --- |
| Install | `POST /api/runtime/engines/{id}/install` |
| Validate | `POST /api/runtime/engines/{id}/validate` |
| Benchmark | `POST /api/runtime/engines/{id}/test` |
| Repair | `POST /api/runtime/engines/{id}/repair` |
| Remove | `DELETE /api/runtime/engines/{id}` |

After each action the center reloads management data.

## Selection controls

Mode change → `PUT /api/runtime/selection` with `{ capabilityModes: { [cap]: mode } }`

Engine pick:

- **Manual** → `{ manualSelections: { [cap]: engineId } }`
- **Locked** → `{ lockedSelections: { [cap]: engineId } }`

## Navigation links

From page header:

- **Runtime Center** → `/settings/runtime`
- **Analytics** → `/settings/runtime/analytics`

## Data contract

TypeScript: `RuntimeEngineManagement`, `RuntimeManagedCapability`, `RuntimeManagedEngine` in `managementTypes.ts`.

Backend response includes `principle`, `configuration`, `recommendations`, `capabilities[]`, `at`.

## Consistency rule

Engine cards use the same resolver `selection-view` as Pipeline Settings and Runtime Dashboard — **current**, **recommended**, and **reference** must match across surfaces.

## Related

- [RUNTIME_ENGINE_MANAGEMENT.md](RUNTIME_ENGINE_MANAGEMENT.md)
- [RUNTIME_ENGINE_SELECTION.md](RUNTIME_ENGINE_SELECTION.md)
- [RUNTIME_DASHBOARD.md](RUNTIME_DASHBOARD.md)
