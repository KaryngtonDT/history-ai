# Personal Server Operations

Version: 1.0

Status: Active

## Overview

The **personal server** is your home PC (or NAS) running Lumen in Docker. This guide covers day-to-day operations for the **Personal Remote** profile.

## Daily checklist

1. PC powered on (or scheduled wake)
2. Docker stack healthy: `make doctor`
3. Tailscale connected on host
4. GPU available if using local models (worker logs)

## Start / stop

```bash
# From repo root
make prod-up      # start stack
make prod-down    # stop stack
make prod-rebuild # after code changes
make migrate      # database migrations
make doctor       # full validation
```

## Monitoring

- Web: `/settings/server` (Sprint 70)
- Mobile: **Server** screen
- CLI: `make doctor`, `docker compose ps`

## Backups

Document local backup paths for:

- Database volume
- `storage/shadow/` file persistence
- Uploaded media (if any)

(Automated backup jobs are out of Sprint 70 MVP — record manual procedure here as you adopt one.)

## Tailscale

See [TAILSCALE_SETUP.md](TAILSCALE_SETUP.md).

## Connected clients

Mobile devices register via `POST /api/shadow/mobile/device`. Presence layer tracks active Shadow clients (Desktop, Browser, Mobile).

## Related

- [HOME_SERVER.md](../architecture/HOME_SERVER.md)
- [DEPLOYMENT_PROFILES.md](../architecture/DEPLOYMENT_PROFILES.md)
