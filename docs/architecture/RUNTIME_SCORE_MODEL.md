# Runtime Score Model

Runtime Score is split into independent metrics. Do not mix them into a single opaque health number.

## Score components

| Score | Meaning |
|-------|---------|
| Core Score | CORE capability readiness |
| Extension Score | OPTIONAL capability readiness |
| Premium Score | PREMIUM capability availability |
| Recommendation Score | Resolver recommendation quality |
| Hardware Compatibility Score | Engine/hardware fit |
| Installation Coverage | Compatible engines installed |
| Benchmark Coverage | Benchmark pass rate |
| Prediction Accuracy | Duration prediction accuracy |

## Dashboard

`GET /api/runtime/dashboard` returns:

- `overallRuntimeScore` — weighted legacy breakdown (core percent drives headline)
- `scoreModel` — split metrics above

## Calculator

`RuntimeScoreCalculator::calculateScoreModel()` derives split scores from platform health and analytics inputs.

Core health headline uses **Core Score only**, not total engine catalog readiness.
