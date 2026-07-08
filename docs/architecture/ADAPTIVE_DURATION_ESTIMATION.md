# Adaptive Duration Estimation

Version: 1.0 (Sprint 70.9)

Status: Planned

---

# Purpose

Replace static rule-only estimates with **history-informed predictions** that improve after every completed pipeline stage.

---

# Components

| Component | Role |
| --------- | ---- |
| `DurationPredictionEngine` | Public API: `estimateForStage(VideoId, PipelineStageType, EngineContext)` |
| `PipelineStageDurationEstimator` | Fallback when history is insufficient |
| `EngineStatistics` | Pre-aggregated medians, P90, error rates |
| `PredictionHistory` | Optional audit of predictions vs outcomes |

---

# Prediction inputs

1. **Stage** — speech_to_text, translation, tts, voice_clone, lip_sync, video_render
2. **Engine** — registry `engineId` + `model`
3. **Hardware profile** — `LOW_END_LOCAL`, `CUDA_NVIDIA`, etc.
4. **Media duration** — seconds from `MediaDurationResolver`
5. **Language** — when relevant (translation, TTS)
6. **Historical executions** — from `EngineExecutionHistory`

---

# Algorithm (v1)

### Step 1 — Sample gate

```text
if executionCount(stage, engine, hardware) >= N_min (default 3):
    use historical predictor
else:
    use blended or rule fallback
```

### Step 2 — Historical predictor

- Primary: **median** `actualDurationSeconds` over last `W` executions (default W=20)
- Conservative UI estimate: **P75** or median × 1.1
- Return `estimatedDurationSeconds`, `estimatedCompletionAt`, `confidence` (0–1)

### Step 3 — Speed-factor blend (sparse history)

```text
predicted = mediaDuration * median(actual/media) from available samples
         OR ruleEstimate if no media duration
```

### Step 4 — Rule fallback

Current `PipelineStageDurationEstimator` formulas (Sprint 70.8).

---

# Confidence

```text
confidence = min(1.0, sampleCount / N_full) * (1 - normalizedVariance)
```

Display in UI only when `confidence >= 0.5`; otherwise show "estimate refining…".

---

# Learning loop

After each `RecordEngineExecutionHandler`:

1. Update `EngineStatistics` for `(stage, engineId, hardwareProfile)`
2. Invalidate in-memory prediction cache for that key
3. Optional: append `PredictionHistory` row

---

# Relative speed score

For a given `(stage, hardwareProfile)`:

```text
relativeSpeed = fastestMedianAcrossEngines / engineMedian
```

Map to 1–5 stars for UI. Fastest engine = 5 stars.

---

# API surface

Orchestrator calls `DurationPredictionEngine` in:

- `getOrCreateJob` — initial estimate
- `startStage` — backfill if missing

Serialized job adds:

- `estimatedCompletionAt` (ISO8601)
- `predictionConfidence` (optional float)

---

# Related

- [ENGINE_EXECUTION_HISTORY.md](ENGINE_EXECUTION_HISTORY.md)
- [ENGINE_ANALYTICS.md](ENGINE_ANALYTICS.md)
