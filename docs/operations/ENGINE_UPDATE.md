# Engine Updates

## Ollama models

```bash
docker compose exec ollama ollama pull gemma3:4b
make runtime-validate
```

## Faster Whisper

Update `STT_FASTER_WHISPER_MODEL` and rerun:

```bash
make provision-engines
```

## Replace Docker shims with real CLIs

1. Install upstream engine on host or extend `infrastructure/docker/backend/Dockerfile`
2. Mount models under `/models/<engine>/`
3. Replace `/usr/local/bin/<engine>` shim
4. Rebuild: `make prod-rebuild`
5. Verify: `POST /api/runtime/engines/{id}/test`

## Full stack refresh

```bash
make prod-rebuild
make provision-engines
make runtime-validate
```
