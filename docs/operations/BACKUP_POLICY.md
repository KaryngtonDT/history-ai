# Backup Policy

## What is backed up

| Component | Method | Location |
| --------- | ------ | -------- |
| PostgreSQL | `pg_dump` (gzip) | `backups/lumen-*/postgres/` |
| Storage | `tar.gz` of `./storage` | `backups/lumen-*/storage/` |
| Configuration | `.env`, compose files | `backups/lumen-*/configuration/` |

Shadow Identity, Learning profiles, uploads, and artifacts live under `./storage`.

## Commands

```bash
make backup
make verify-backup
make restore
```

## Rotation

Backups are timestamped: `backups/lumen-YYYYMMDD-HHMMSS/`.

`backups/latest` symlink points to the most recent backup.

## Recommended schedule

| Environment | Frequency |
| ----------- | --------- |
| Local dev | Before major changes |
| Staging | Daily |
| Production | Daily + before deployments |

## Retention

Keep at least:

- 7 daily backups
- 4 weekly backups

Delete old `backups/lumen-*` directories manually or via cron.
