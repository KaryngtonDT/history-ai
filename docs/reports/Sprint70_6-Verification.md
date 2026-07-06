# Sprint 70.6 — Runtime Intelligence Dashboard — Verification

## Scope

- `GET /api/runtime/dashboard` — live aggregated dashboard
- `RuntimeHealthDashboard` — primary `/settings/runtime` view
- Weighted Runtime Score and Platform Score
- Shadow commentary from real compatibility state

## Checks

| Check | Result |
| --- | --- |
| Backend unit: `RuntimeScoreCalculatorTest` | pass |
| Backend functional: `testRuntimeDashboardAggregatesLiveRuntimeData` | pass |
| Frontend: `RuntimeHealthDashboard.test.tsx` | pass |
| Docker / PHPUnit / Vitest | pass |

## Notes

Dashboard data is assembled only from Runtime platform services, benchmark history, validation reports, and platform health checks. Premium engine blocks surface hardware reasons and alternatives (e.g. Wav2Lip when LatentSync requires CUDA).
