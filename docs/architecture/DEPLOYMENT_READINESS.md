# Deployment Readiness & Disaster Recovery

Status: Active (Platform Sprint 59)

## Storage architecture

All persistent files live in the Docker named volume `history-ai-lumen-storage`, mounted at `/var/www/html/storage` in the backend container.

Optional bind mount via `docker-compose.storage-bind.yml` on Linux/macOS.

```
storage/
├── uploads/          video, audio, pdf
├── artifacts/        pipeline outputs
├── shadow/           identity + sessions (JSON)
├── learning/         adaptive profiles (JSON)
├── workspace/
├── logs/
├── temp/
└── cache/
```

AI model weights live under `./models` (mounted at `/models`) — never in Docker images.

## Persistence

| Domain | Repository | Path |
| ------ | ---------- | ---- |
| LearningProfile | `FileLearningProfileRepository` | `storage/learning/*.json` |
| ShadowIdentity | `FileShadowIdentityRepository` | `storage/shadow/identity/*.json` |
| ShadowSession | `FileShadowSessionRepository` | `storage/shadow/sessions/*.json` |

## Health & readiness

| Endpoint | Purpose |
| -------- | ------- |
| `GET /health` | Liveness |
| `GET /ready` | Postgres, storage, worker, models |
| `GET /live` | Readiness + disk space |
| `GET /api/platform/readiness` | Production readiness score |

## Operations

- [DEPLOYMENT_GUIDE.md](../operations/DEPLOYMENT_GUIDE.md)
- [BACKUP_POLICY.md](../operations/BACKUP_POLICY.md)
- [RESTORE_GUIDE.md](../operations/RESTORE_GUIDE.md)
- [PRODUCTION_CHECKLIST.md](../operations/PRODUCTION_CHECKLIST.md)
- [DISASTER_RECOVERY.md](../operations/DISASTER_RECOVERY.md)
- [MAKEFILE_GUIDE.md](../Development/MAKEFILE_GUIDE.md)

## Docker

- `docker-compose.yml` — development (with override)
- `docker-compose.prod-like.yml` — production-like (no source bind mounts)

```bash
make prod          # prod-like up
make backup        # pg_dump + storage archive
make doctor        # full diagnostic
```

## Volume rules

| Command | Data preserved? |
| ------- | --------------- |
| `docker compose up --build` | Yes |
| `docker compose down` | Yes |
| `docker compose down -v` | No (postgres, redis, minio volumes) |
| `./storage` bind mount | Survives `down -v` |
