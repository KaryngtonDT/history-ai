# Runtime Engine Management

Lumen's **AI Engine Management Platform** (Platform Sprint 72) centralizes install, update, repair, removal, validation, and benchmarking behind a single lifecycle façade.

## Principle

```text
Runtime decides.
Worker executes.
UI observes.
```

## Architecture

```text
RuntimeController
        │
RuntimePlatformService
        │
RuntimeEngineLifecycleService
        │
┌───────┼───────┬───────────┬────────────┐
│       │       │           │            │
Provision Update Repair  Removal   Notification
Manager  Manager Manager  Manager   Service
        │
Version / Dependency / Model managers
        │
EngineProvisioner + BenchmarkRunner + ReadinessEngine
```

## Lifecycle façade

`RuntimeEngineLifecycleService` (`Infrastructure/Runtime/Lifecycle/`) orchestrates:

| Operation | Manager | API |
| --------- | ------- | --- |
| Install | `RuntimeProvisionManager` | `POST /api/runtime/engines/{id}/install` |
| Update | `RuntimeUpdateManager` | `POST /api/runtime/engines/{id}/update` |
| Repair | `RuntimeRepairManager` | `POST /api/runtime/engines/{id}/repair` |
| Remove | `RuntimeRemovalManager` | `DELETE /api/runtime/engines/{id}` |
| Enable / Disable | `RuntimeRemovalManager` | internal |
| Validate | lifecycle + readiness | `POST /api/runtime/engines/{id}/validate` |
| Benchmark | `BenchmarkRunner` | `POST /api/runtime/engines/{id}/test` |

Supporting metadata:

- `RuntimeVersionManager` — installed version string
- `RuntimeDependencyManager` — pip/system dependencies
- `RuntimeModelManager` — model/checkpoint paths and sizes

## Management assembly

`RuntimeEngineManagementAssembler` builds the **management payload** for UI and Shadow:

- Iterates all `EngineCatalogCapability` cases
- Merges discovery, compatibility, health, analytics, selection-view
- Returns capability groups with engine cards

**API:** `GET /api/runtime/engines/management`

## Install flow

1. `RuntimeProvisionManager::install()` — venv, models, dependencies
2. Readiness re-evaluation via discovery scanners
3. Optional benchmark via `BenchmarkRunner`
4. Analytics registration (execution history baseline)
5. `RuntimeNotificationService::record('engine_installed', …)`

## Configuration store

Selection and disabled engines live in `configuration.json` via `FileRuntimeRepository`:

- `capabilityModes`, `manualSelections`, `lockedSelections`
- `disabledEngines` — engines excluded from management views

## UI entry point

**Settings → Runtime → AI Engine Manager** — `/settings/runtime/engines`

Component: `RuntimeProvisionCenter` — see [RUNTIME_PROVISION_CENTER.md](RUNTIME_PROVISION_CENTER.md).

## Related

- [RUNTIME_KERNEL.md](RUNTIME_KERNEL.md) — kernel layers
- [RUNTIME_RESOLVER.md](RUNTIME_RESOLVER.md) — selection decision point
- [RUNTIME_ENGINE_SELECTION.md](RUNTIME_ENGINE_SELECTION.md) — Auto/Manual/Locked
