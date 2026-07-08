# Engine Analytics

Version: 1.0 (Sprint 70.9)

Status: Planned

---

# Purpose

Expose engine performance intelligence to users at **`/settings/runtime/analytics`** and enrich pipeline stage cards with prediction vs reality.

---

# UI surfaces

## 1. Pipeline stage card

Per active/completed job — see TASK-0070.9 SLICE-03.

## 2. Runtime analytics dashboard

**Route:** `/settings/runtime/analytics`

### Per-engine summary card

| Metric | Source |
| ------ | ------ |
| Executions | `COUNT(*)` from history |
| Average duration | `AVG(actualDurationSeconds)` |
| Median duration | `PERCENTILE_CONT(0.5)` |
| Fastest / slowest | `MIN` / `MAX` |
| Average estimation error | `AVG(abs(estimationErrorSeconds))` |
| Success rate | completed / (completed + failed) |
| Failure rate | failed / total |
| **Relative speed** | stars from `EngineStatistics.relativeSpeedScore` |
| Hardware profiles | grouped breakdown |

### Example (STT)

```text
Faster Whisper Large V3
  Executions   42
  Average      31 min
  Fastest      22 min
  Slowest      61 min
  Avg error    4 %
  Success      100 %
  Speed        ⭐⭐⭐⭐☆
```

### Benchmark history

Sparkline or table of last N executions (duration + accuracy).

---

# Shadow integration

Shadow reads aggregates + recent executions to answer:

- duration questions for current video/stage
- why estimate changed (new samples, different engine, hardware drift)
- engine comparison on current hardware profile
- recommendation with relative speed trade-off

Context builder: `EngineAnalyticsContextBuilder` (SLICE-05).

---

# API

```text
GET /api/runtime/analytics/engines
GET /api/runtime/analytics/engines/{engineId}?stage=&hardwareProfile=
GET /api/runtime/analytics/engines/{engineId}/history?limit=50
```

Response includes `relativeSpeedScore` (1–5) and `relativeSpeedLabel` (optional star string).

---

# Frontend module

```text
frontend/src/features/runtime/RuntimeAnalyticsDashboard/
frontend/src/services/runtime/HttpRuntimeRepository.ts  # extend
```

i18n: `runtime.analytics.*` in en/fr/de.

---

# Related

- [ENGINE_EXECUTION_HISTORY.md](ENGINE_EXECUTION_HISTORY.md)
- [ADAPTIVE_DURATION_ESTIMATION.md](ADAPTIVE_DURATION_ESTIMATION.md)
- Runtime Center (Sprint 70.4) — health & capabilities
