# Restore Guide

## Full restore procedure

```text
Machine vierge
    ↓
git clone
    ↓
cp .env.example .env
    ↓
make prod
    ↓
make restore
    ↓
make doctor
```

## Restore from specific backup

```bash
bash scripts/restore.sh backups/lumen-20260702-120000
```

## Verification checklist

After restore, `make doctor` should report:

| Check | Expected |
| ----- | -------- |
| `/health` | `status: ok` |
| `/ready` | `status: ready` |
| `/live` | `status: live` |
| Readiness score | ≥ 90 |

Manual checks:

- Videos accessible in library
- Shadow settings at `/settings/shadow` preserved
- Learning profile at `/settings/learning` preserved
- Workspace projects visible

## Troubleshooting

**Postgres restore fails** — ensure `make prod` is running and postgres is healthy.

**Storage empty after restore** — verify `storage/storage.tar.gz` exists in backup.

**Permission errors** — run `chmod -R 775 storage` on Linux hosts.
