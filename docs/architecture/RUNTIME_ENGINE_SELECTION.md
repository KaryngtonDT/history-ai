# Runtime Engine Selection

Engine selection is **per capability**, stored in the Runtime configuration store, and enforced by the **Runtime Resolver**.

## Modes

`CapabilitySelectionMode` (`Domain/Engine/CapabilitySelectionMode.php`):

| Mode | Value | Semantics |
| ---- | ----- | --------- |
| **Auto** | `auto` | Runtime decides using full policy chain |
| **Manual** | `manual` | User picks engine; stored in `manualSelections` |
| **Locked** | `locked` | Fixed engine in `lockedSelections`; never auto-switch |

UI labels: Auto, Manual, Locked — toggled per capability in Provision Center.

## Configuration model

`RuntimeConfiguration` (`configuration.json` via `FileRuntimeRepository`):

```json
{
  "profile": "balanced",
  "selectionMode": "auto",
  "capabilityModes": {
    "speech_to_text": "manual",
    "lip_sync": "locked"
  },
  "manualSelections": {
    "speech_to_text": "faster_whisper_large_v3"
  },
  "lockedSelections": {
    "lip_sync": "latentsync"
  },
  "disabledEngines": []
}
```

| Field | Scope |
| ----- | ----- |
| `selectionMode` | Global default (maps to capability mode when no override) |
| `capabilityModes` | Per-capability override |
| `manualSelections` | Engine ID when Manual |
| `lockedSelections` | Engine ID when Locked |
| `disabledEngines` | Excluded from UI and resolution |

## Resolver policy

`RuntimeResolver::pickEngineId()` priority:

```text
1. Locked selection     (if mode = locked)
2. Manual selection     (if mode = manual)
3. Planner preferred    (context.preferredEngineId)
4. Hardware recommended (HardwareReportBuilder pipeline key)
5. Profile recommended  (RecommendationEngine)
6. Ops bootstrap        (env-configured engine)
7. Catalog default      (EngineCatalogDefinitions)
```

**Fallback:** When primary is blocked and mode ≠ Locked, resolver may switch to `RuntimeFallbackPlan` with reason `fallback`.

## API

| Method | Path | Purpose |
| ------ | ---- | ------- |
| GET | `/api/runtime/selection` | Current config + resolved video capabilities |
| PUT | `/api/runtime/selection` | Update modes and selections |
| GET | `/api/runtime/capabilities/{cap}/selection-view` | UI payload per capability |
| POST | `/api/runtime/resolve` | Contextual resolve with intelligence |

## UI surfaces

| Surface | Selection data source |
| ------- | --------------------- |
| Provision Center | management API + `PUT /selection` |
| Pipeline Settings | `selection-view` per stage |
| Runtime Dashboard | `selection-view` / dashboard assembler |
| Doctor | `selection-view` per capability |

## Pipeline sync

`RuntimeSelectionSynchronizer` dual-writes manual selections to legacy `pipeline_configuration` on pipeline save (transition — removal deferred).

## Rules

- Pipeline **never** chooses engines — it requests capabilities; Resolver decides.
- Locked engines survive hardware/profile changes until user unlocks.
- Blocked manual/locked selections surface `blockedReason` without silent substitution (except explicit fallback in Auto mode).

## Related

- [RUNTIME_RESOLVER.md](RUNTIME_RESOLVER.md)
- [RUNTIME_RESOLVER_INTELLIGENCE.md](RUNTIME_RESOLVER_INTELLIGENCE.md)
- [RUNTIME_PROVISION_CENTER.md](RUNTIME_PROVISION_CENTER.md)
