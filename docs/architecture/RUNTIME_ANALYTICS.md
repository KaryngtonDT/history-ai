# Runtime Analytics

Runtime Analytics turns every engine execution into **measurable intelligence** that feeds resolver estimates, recommendation profiles, and management UI metrics.

## Foundation

Delivered in Platform Sprint **70.9**; integrated into Sprint **72** management platform.

## Learning loop

```text
Pipeline / Worker execution
        │
Engine execution record (PostgreSQL)
        │
EngineStatisticsAggregator
        │
┌───────┼───────┬──────────────────┐
│       │       │                  │
Resolver  Provision  Analytics
Intelligence Center   Dashboard
```

## Stored metrics (per execution)

| Metric | Use |
| ------ | --- |
| Real duration | Rankings, median estimates |
| Estimated duration | Prediction error calculation |
| Prediction error | Adaptive `DurationPredictionEngine` |
| Success / failure | Success rate, failure rate |
| Language, media duration | Context-aware estimates |
| Engine version | Version drift detection |
| Hardware profile | Hardware statistics |
| Pipeline stage / capability | Per-capability aggregates |

## Aggregator

`EngineStatisticsAggregator` (`Application/EngineAnalytics/`) produces per-engine:

- `medianDurationSeconds`, `averageDurationSeconds`
- `successRate`, `executionCount`
- `relativeSpeedLabel` (benchmark score proxy)

Consumed by:

- `RuntimeEngineManagementAssembler` — engine card stats
- `RuntimeResolverIntelligence` — duration/accuracy estimates
- `RuntimeRecommendationProfilesService` — fastest profile

## UI

**Route:** `/settings/runtime/analytics`

**Component:** `RuntimeAnalyticsDashboard`

**API:** `RuntimeAnalyticsController` under `/api/runtime/analytics/*`

**Sections:**

- Engine rankings by stage/capability
- Execution history timeline
- Prediction accuracy (estimated vs real)
- Capability-level statistics
- Duration and success rate summaries

## Management integration

Provision Center engine cards show:

- Benchmark score (`relativeSpeedLabel`)
- Average duration (seconds)
- Success rate (%)
- Execution count

## Resolver integration

`RuntimeResolverIntelligence::findAnalytics()` looks up stage-mapped capability history to populate:

- `estimatedDurationSeconds`
- `expectedAccuracy`
- `alternativeEngineId` (next-fastest or next-ready candidate)

## Related

- [RUNTIME_RESOLVER_INTELLIGENCE.md](RUNTIME_RESOLVER_INTELLIGENCE.md)
- [RUNTIME_RECOMMENDATIONS.md](RUNTIME_RECOMMENDATIONS.md)
- [RUNTIME_PROVISION_CENTER.md](RUNTIME_PROVISION_CENTER.md)
