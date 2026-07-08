# Engine Execution History

Version: 1.0 (Sprint 70.9)

Status: Planned

---

# Purpose

Record **every pipeline stage execution** as an immutable benchmark row. This is the production truth layer for runtime learning — distinct from:

| System | Granularity | Mutable |
| ------ | ----------- | ------- |
| `ExecutionHistory` | Whole video pipeline versions | Append versions per video |
| `BenchmarkRunner` | Synthetic host benchmarks | Overwrites report files |
| **`EngineExecutionHistory`** | **Per stage + engine + job** | **Append-only** |

---

# Principles

1. **Never overwrite** — each completion creates a new `executionId`.
2. **Capture at completion** — `actualDurationSeconds` from `startedAt` / `completedAt` on `PipelineJob`.
3. **Snapshot estimate at start** — `estimatedDurationSeconds` frozen when the job enters `running`.
4. **Hardware context matters** — store `hardwareProfile` from runtime detection at execution time.
5. **Link to job** — `pipelineJobId` enables UI drill-down from pipeline cards.

---

# Data model

See [TASK-0070.9](../../planning/Platform/Sprint-70.9/TASK-0070.9.md) SLICE-01 for the full field list.

### Derived metrics

```text
estimationErrorSeconds   = actualDurationSeconds - estimatedDurationSeconds
estimationAccuracyPercent = max(0, 100 - abs(error) / max(estimated, 1) * 100)
```

### Status values

- `completed` — stage finished successfully
- `failed` — stage failed (still record duration if `startedAt` present)
- `cancelled` — user cancelled (optional: exclude from speed rankings)

---

# Write path

```text
PipelineOrchestrator::completeStage / failStage / cancelStage
        │
        ▼
RecordEngineExecutionHandler
        │
        ▼
engine_execution_history (INSERT only)
        │
        ▼
EngineStatisticsAggregator (async or sync)
```

---

# Read path

- **Pipeline UI** — latest execution for active/completed job
- **Runtime analytics** — aggregate queries by `engineId`, `stage`, `hardwareProfile`
- **Shadow** — natural-language answers from recent history + aggregates
- **DurationPredictionEngine** — rolling window per engine profile

---

# Retention

- No automatic deletion in v1.
- Future: optional archival policy (ADR required before any purge).

---

# Related

- [ADAPTIVE_DURATION_ESTIMATION.md](ADAPTIVE_DURATION_ESTIMATION.md)
- [ENGINE_ANALYTICS.md](ENGINE_ANALYTICS.md)
- Platform Sprint 70.8 — `PipelineJob` orchestration
