# Pipeline Stage Confirmation

After each stage completes, job status becomes `waiting_user_confirmation`.

The next stage does **not** start until the user calls:

```
POST /api/pipeline/jobs/{sourceId}/{stage}/continue
```

Automatic multi-stage runs remain available when `ProcessingMode::Automatic` is explicitly requested (batch/legacy paths).

Applies between: transcription → translation → audio → voice clone → lip sync → render → quality.
