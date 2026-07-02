# Disaster Recovery

## Recovery objectives

| Metric | Target |
| ------ | ------ |
| RTO (recovery time) | < 30 minutes |
| RPO (data loss) | < 24 hours (daily backup) |

## Scenarios

### Backend container crash

```bash
make prod-backend
```

Data preserved via `./storage` bind mount.

### Full Docker destruction

```bash
git clone <repo>
cp .env.example .env
make prod
make restore
```

### Accidental `docker compose down -v`

Database and MinIO volumes are lost. Restore from backup:

```bash
make prod
make restore
```

`./storage` bind mount survives `down -v`.

### Corrupted storage

```bash
make restore
# or restore storage tarball only:
tar -xzf backups/latest/storage/storage.tar.gz
```

## Emergency contacts

Document your hosting provider, DNS, and SSL certificate renewal process here before production.

## Test schedule

Run full restore drill **monthly**:

1. Provision clean VM
2. Clone + `make prod` + `make restore`
3. Verify checklist in RESTORE_GUIDE.md
