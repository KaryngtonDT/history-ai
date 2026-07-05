# Engine Troubleshooting

## Runtime shows BLOCKED

1. Open `/settings/runtime` — read `errorReason` and `installCommand`
2. Run `make provision-engines` for auto-supported engines
3. For manual engines see `ENGINE_INSTALLATION.md`

## Ollama model not found

```bash
docker compose exec ollama ollama list
docker compose exec ollama ollama pull gemma3:4b
curl http://localhost:8000/api/runtime/engines/ollama_gemma3/test -X POST
```

## Faster Whisper fails

- Confirm `STT_FASTER_WHISPER_MODEL=large-v3`
- Prefetch model (see `ENGINE_MODELS.md`)
- Check backend logs for HF download errors

## Shim engines (F5, OpenVoice, LatentSync)

Status `blocked` with shim reason until real weights are mounted. This is expected in dev Docker.

## Pipeline validation fails

```bash
make runtime-validate
```

Each step lists `requestedEngineId`, `executedEngineId`, `mode`, `reason` — no silent fallback.

## Still stuck

Run `make doctor` and attach output to an issue.
