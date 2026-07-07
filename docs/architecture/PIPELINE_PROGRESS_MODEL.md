# Pipeline Progress Model

## Job fields

- `progressPercent` (0–100)
- `estimatedDurationSeconds` / `estimatedRemainingSeconds` / `elapsedSeconds`
- `currentStep` — human-readable step label
- `status` — includes `waiting_user_confirmation` and `waiting_user_choice`

## Progress sources

1. **Measured** — worker updates during long STT (`extracting_audio`, `saving_transcript`)
2. **Estimated** — heartbeat extrapolates from elapsed vs estimated duration when segment progress unavailable
3. **Terminal** — 100% on stage completion

## UI contract

Shadow Watch and `PipelineProgressPanel` poll `/api/pipeline/jobs/{sourceId}` every 3–5 seconds. No fixed 5-minute failure while a background STT job is `running` or `queued`.
