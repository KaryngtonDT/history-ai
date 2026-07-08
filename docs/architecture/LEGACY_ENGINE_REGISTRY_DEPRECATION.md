# Legacy Engine Registry Deprecation

Sprint 71.1 introduces the Runtime Kernel without removing legacy components. This document tracks deprecation and removal criteria.

## Deprecated components

| Component | Replacement | Status |
| --------- | ----------- | ------ |
| `AIEngineRegistry` | `EngineRepositoryInterface` + `RuntimeResolver` | Deprecated, retained |
| `AIEngineRegistryFactory` | `EngineCatalogDefinitions` + `EngineAdapterRegistry` | Deprecated, retained |
| `GET /api/ai/providers` | `GET /api/runtime/capabilities/{capability}/selection-view` | Deprecated flag in response |
| `AIEngineConfiguration` defaults | Runtime Selection Store | Deprecated for SSOT |
| `PipelineDefaultProviders` | Catalogue defaults + resolver policy | Deprecated |
| Env vars as user “current” | Ops bootstrap only (`STT_PROVIDER`, …) | Semantic deprecation |

## Still required (execution layer)

These are **not** deprecated:

| Component | Role |
| --------- | ---- |
| `SpeechToTextProviderFactory`, etc. | Technical instantiation |
| `AIProviderResolver` | Façade — routes to kernel or legacy |
| `EngineExecutionAdapter` | Bridge plan → factory |

## Migration path

### Phase 1 — Sprint 71.1 (complete)

- [x] `RuntimeResolver` + `EngineExecutionAdapter`
- [x] Pipeline Settings → selection-view API
- [x] `RuntimeSelectionSynchronizer` on pipeline save
- [x] Doctor → Runtime API
- [x] Planner → Runtime client
- [x] `RUNTIME_KERNEL_UNIFIED` feature flag (default true)

### Phase 2 — Follow-up (planned)

- [ ] Frontend `AIEngineSettings` → Runtime capabilities API
- [ ] `PUT /api/runtime/selection` in Runtime Settings UI
- [ ] Remove dual-write; PostgreSQL `pipeline_configuration` read-only or dropped
- [ ] Worker Python → `POST /api/runtime/resolve`
- [ ] Shadow/Mobile explicit resolve client

### Phase 3 — Removal (planned)

- [ ] Delete `AIEngineRegistry`, `AIEngineRegistryFactory`
- [ ] Remove `GET /api/ai/providers`
- [ ] Remove legacy branch in `AIProviderResolver`
- [ ] Drop `PipelineDefaultProviders`

## Removal criteria (all must be true)

1. Every consumer calls `POST /api/runtime/resolve` or `selection-view`
2. Pipeline execution always uses `EngineExecutionAdapter` (flag removed or always on)
3. Doctor uses Runtime readiness only (no bash binary checks)
4. No CI test asserts legacy provider IDs as SSOT
5. OpenAPI documents Runtime APIs as canonical
6. One sprint of production usage without legacy flag rollback

## API migration hints

`GET /api/ai/providers` response includes:

```json
{
  "deprecated": true,
  "migration": "Use GET /api/runtime/capabilities/{capability}/selection-view and POST /api/runtime/resolve"
}
```

## Logging

Pipeline configuration save triggers `RuntimeSelectionSynchronizer` — manual selections written to `configuration.json`.

Consider adding metric: `legacy_registry_hit` when `RUNTIME_KERNEL_UNIFIED=false`.

## Risk of premature removal

Removing `AIEngineRegistry` before factory bridge is complete breaks:

- All `Video*Generator` handlers
- `ProcessVideoHandler` / `ProcessAudioHandler`
- Pipeline Settings save validation
- `DeterministicPipelinePlanner` (if flag off)
- OpenAPI clients and frontend `AIEngineSettings`

**Do not remove until Phase 3 criteria are met.**
