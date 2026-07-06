# Engine Compatibility Matrix

**Updated:** Sprint 70.7 (post-dashboard completion)  
**Source of truth:** Runtime Dashboard + `EngineRequirementMatrix` + live compatibility API

## How to read this matrix

| Column | Meaning |
| --- | --- |
| Reference | Catalog default (`EngineCatalogDefinitions`) |
| Recommended | Hardware pipeline (`GET /api/runtime/hardware` → `recommendedPipeline`) |
| Current | Configured engine for the capability |
| HW compat | From `GET /api/runtime/compatibility` |
| Status | READY / BLOCKED / misconfigured from readiness |

## Per-profile summary

See [Hardware-Recommendation-Matrix.md](./Hardware-Recommendation-Matrix.md) for full pipeline tables per profile.

## API

```http
GET /api/runtime/dashboard
GET /api/runtime/completion/plan
GET /api/runtime/compatibility
```

## Rules (Sprint 70.7)

1. **BLOCKED** premium engines stay in the registry — never removed.
2. Completion provisions only **recommended** + hardware-compatible + not-READY engines.
3. No hardware re-detection during completion (`hardwareRedetected: false`).

Detailed operation guides: [docs/operations/ENGINE_COMPATIBILITY.md](../operations/ENGINE_COMPATIBILITY.md).
