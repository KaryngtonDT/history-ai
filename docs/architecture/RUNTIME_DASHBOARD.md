# Runtime Intelligence Dashboard

The Runtime Intelligence Dashboard is the primary view at `/settings/runtime`. It aggregates live Runtime data into an explainable health center — no simulated metrics.

## API

`GET /api/runtime/dashboard` returns:

- **overallRuntimeScore** — weighted score with breakdown
- **platformScore** — Runtime, Shadow, Storage, Worker, API, Docker, Documentation
- **summary** — profile, provisioning, compatible/premium counts, last validation
- **capabilityStatuses** / **capabilityScores** — per-capability readiness
- **hardware** — detected profile and utilization hints
- **engineRecommendations** — reference, recommended, and current engines
- **premiumFeatures** — blocked premium engines with hardware needs
- **timeline** — benchmarks, validations, installs
- **warnings** — blocked engines with human reasons
- **recommendations** — recommended local pipeline
- **shadowCommentary** — Shadow narrative from live state

## Architecture

Bounded context:

- `Application/RuntimeDashboard/` — calculators and interface
- `Domain/RuntimeDashboard/` — score value objects
- `Infrastructure/RuntimeDashboard/RuntimeDashboardAssembler` — aggregates Runtime platform, compatibility, benchmarks, validation history, and platform health
- `Presentation/Http/Controller/RuntimeDashboard/RuntimeDashboardController`

## Data sources

All fields are derived from:

- `RuntimePlatformService` (readiness, health, hardware, compatibility, recommendations, provisioning)
- `BenchmarkRunner::history()`
- `RuntimeRepositoryInterface::listValidationReports()` and `listExecutions()`
- `PlatformHealthCheckerInterface::productionReadiness()`
- `CapabilityMaturityRegistry`

## UI

`RuntimeHealthDashboard` is rendered above `RuntimeCenter` on the Runtime settings page. The console below retains validate, benchmark, and provision actions.

See also: [RUNTIME_SCORE.md](./RUNTIME_SCORE.md).

## Runtime Completion (Sprint 70.7)

```http
GET  /api/runtime/completion/plan
POST /api/runtime/completion/execute
```

The completion planner reads the dashboard and readiness registry — it does **not** re-detect hardware. Only **recommended**, hardware-compatible, not-READY engines with auto-provision support are installed.

After execution, `docs/reports/Runtime-Technology-Review-After70_6.md` and `Engine-Provisioning-Final.md` are regenerated from live scores.
