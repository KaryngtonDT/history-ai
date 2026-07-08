# Runtime Platform Health

Platform health separates **Runtime Core Health** from extension and premium coverage.

## Metrics

| Metric | Scope |
|--------|--------|
| Runtime Core Health | CORE capabilities only |
| Extension Coverage | OPTIONAL capabilities |
| Premium Availability | PREMIUM capabilities |
| Experimental Coverage | EXPERIMENTAL capabilities |
| Deprecated Count | DEPRECATED capabilities |

## Rules

- Blocked OPTIONAL or PREMIUM capabilities **never** reduce Core Health.
- Doctor reports `coreStatus: ready` when all CORE capabilities are operational.
- `make runtime-validate` exits **0** when Core Runtime is `ready`, even if optional engines are missing.

## API

- `GET /api/runtime/health` — includes `platformHealth`
- `GET /api/runtime/dashboard` — includes `platformHealth` and `scoreModel`
- `GET /api/runtime/doctor` — includes `coreStatus` and classified capabilities
- `POST /api/runtime/pipeline/validate` — returns `coreRuntime`, `extensions`, `premium`, `experimental`

## Service

`RuntimePlatformHealthService` evaluates capability availability using resolver + compatibility data and produces consistent classification-aware health sections.
