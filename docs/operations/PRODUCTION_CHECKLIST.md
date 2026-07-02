# Production Checklist

Use before inviting beta testers or deploying to a VPS.

## Infrastructure

- [ ] `make prod` starts all services healthy
- [ ] `make doctor` passes
- [ ] Production readiness score ≥ 90
- [ ] `./storage` bind mount configured
- [ ] `./models` bind mount configured (AI weights outside images)
- [ ] `.env` secrets changed from defaults (`APP_SECRET`, DB passwords)

## Data persistence

- [ ] `docker compose up --build` preserves data
- [ ] `docker compose down` preserves data
- [ ] Shadow Identity survives backend rebuild
- [ ] Learning profile survives backend rebuild
- [ ] Uploads and artifacts survive rebuild

## Backup & recovery

- [ ] `make backup` completes successfully
- [ ] `make verify-backup` passes
- [ ] Restore tested on clean machine (see RESTORE_GUIDE.md)

## Monitoring

- [ ] `/health`, `/ready`, `/live` respond
- [ ] Disk space ≥ 5 GB free
- [ ] Worker `/health` responds

## Documentation

- [ ] Team knows `make help`
- [ ] `make prod-fresh` requires `DELETE EVERYTHING` confirmation
