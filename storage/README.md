# Lumen Storage

Persistent data lives in the Docker named volume `history-ai-lumen-storage`, mounted at `/var/www/html/storage` in the backend container.

## Layout

| Path | Purpose | Retention |
| ---- | ------- | --------- |
| `uploads/video` | Uploaded video files | Until deleted |
| `uploads/audio` | Uploaded audio sources | Until deleted |
| `uploads/pdf` | PDF content files | Until deleted |
| `artifacts/*` | Pipeline outputs (transcript, translation, audio, …) | Until deleted |
| `shadow/identity` | Shadow Identity profiles (JSON) | Persistent |
| `shadow/sessions` | Shadow watch sessions (JSON) | Persistent |
| `learning` | Adaptive learning profiles (JSON) | Persistent |
| `workspace` | Workspace exports | Until deleted |
| `logs` | Application logs | Rotated |
| `temp` | Temporary processing files | Cleaned periodically |
| `cache` | Local cache | Safe to delete |

## Docker (default — named volumes)

```yaml
volumes:
  lumen_storage:
    name: history-ai-lumen-storage
```

`docker compose up --build` preserves this volume. Only `docker compose down -v` removes it.

## Optional bind mount (Linux/macOS)

```bash
docker compose -f docker-compose.yml -f docker-compose.storage-bind.yml up -d
```

On Windows hosts with spaces in the project path, use named volumes (default).
