# Sprint 71.1 — Runtime Kernel Unification — Verification

## Summary

Sprint 71.1 delivers **convergence** between the Runtime platform (Sprint 70.x + 71) and the legacy Pipeline engine stack (Sprint 34–39).

**Objective met:** Runtime Resolver is the decision point when `RUNTIME_KERNEL_UNIFIED=true` (default).

---

## Backend

| Item | Status | Notes |
| ---- | ------ | ----- |
| `RuntimeResolver` + domain types | ✅ | `ResolvedEngine`, `EngineExecutionPlan`, `RuntimeResolveReason` |
| `EngineAdapterRegistry` | ✅ | Catalogue ID ↔ legacy provider key |
| `PipelineStageCapabilityMapper` | ✅ | 6 video stages |
| `POST /api/runtime/resolve` | ✅ | `RuntimeController` |
| `GET /api/runtime/selection` | ✅ | |
| `PUT /api/runtime/selection` | ✅ | API exists; frontend UI deferred |
| `GET /api/runtime/capabilities/{capability}/selection-view` | ✅ | |
| `RuntimeSelectionSynchronizer` | ✅ | On pipeline save |
| `EngineExecutionAdapter` | ✅ | |
| `AIProviderResolver` kernel path | ✅ | `$runtimeKernelUnified` DI param |
| `DeterministicPipelinePlanner` → Resolver | ✅ | |
| `GET /api/ai/providers` deprecated marker | ✅ | |

## Frontend

| Item | Status | Notes |
| ---- | ------ | ----- |
| `PipelineBuilder` → selection-view | ✅ | Per stage parallel fetch |
| `PipelineStageSelector` metadata | ✅ | Recommended, Current, Installed, Blocked |
| Reference engine in Pipeline UI | ⚠️ | Dashboard shows Reference; Pipeline stage omits it |
| `AIEngineSettings` → legacy API | ⚠️ | Still uses `/api/ai/providers` |

## Ops

| Item | Status | Notes |
| ---- | ------ | ----- |
| `doctor.sh` → Runtime readiness | ✅ | |
| `doctor.sh` → selection-view per stage | ✅ | Replaces hardcoded binary checks |
| Platform readiness | ✅ | Unchanged |

## Commands

```bash
make test-backend
make test-frontend
make doctor
make runtime-validate
make runtime-benchmark
make prod-rebuild
```

---

## Acceptance checklist

| Criterion | Status |
| --------- | ------ |
| Runtime = decision point (kernel unified) | ✅ |
| Pipeline Settings consumes Runtime | ✅ |
| Doctor consumes Runtime | ✅ |
| Planner consumes Runtime | ✅ |
| Legacy registry functional (no removal) | ✅ |
| Blocked engines visible with reason | ✅ |
| Executed provider = resolved adapterKey | ✅ |
| No silent fallback (blocked → exception) | ✅ |
| Dashboard ≡ Pipeline semantics | ⚠️ Reference field gap in Pipeline UI |
| Worker Python on Runtime | ❌ Deferred |
| Legacy removal | ❌ Deferred |

---

## Known gaps (follow-up)

1. **Worker Python** — still uses local `AIProviderFactory`; not Runtime client.
2. **Runtime Settings UI** — no form for `PUT /api/runtime/selection` (manual mode).
3. **Reference engine** — Pipeline stage metadata does not show Reference line (Dashboard does).
4. **`pipeline_configuration` table** — still SSOT for legacy API; dual-write only on save.
5. **Capability Registry expansion** — non-video future capabilities documented, not integrated in Pipeline.

---

## Architecture documents

- [RUNTIME_KERNEL.md](../architecture/RUNTIME_KERNEL.md)
- [RUNTIME_RESOLVER.md](../architecture/RUNTIME_RESOLVER.md)
- [ENGINE_EXECUTION_PLAN.md](../architecture/ENGINE_EXECUTION_PLAN.md)
- [PIPELINE_RUNTIME_INTEGRATION.md](../architecture/PIPELINE_RUNTIME_INTEGRATION.md)
- [LEGACY_ENGINE_REGISTRY_DEPRECATION.md](../architecture/LEGACY_ENGINE_REGISTRY_DEPRECATION.md)
