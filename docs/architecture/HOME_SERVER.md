# Home Server Architecture

Version: 1.0

Status: Active (Sprint 70)

## Definition

A **Home Server** is the single machine running the authoritative Lumen stack:

```text
Docker Compose (prod-like)
  ├── backend (Symfony)
  ├── frontend (static)
  ├── worker (Python)
  ├── database
  ├── redis / messenger
  └── nginx
```

Plus host resources:

- GPU (CUDA) for local models
- Local storage / backups
- Tailscale daemon (Personal Remote profile)

## Why home-first

During Phase III (~6 months before public API), the primary operator hosts Lumen at home. Benefits:

- No cloud bill for inference
- Data and Second Brain stay on owned hardware
- Same environment as `make doctor` / Docker validation
- Mobile access via Tailscale without public exposure

## Personal Server Dashboard

Routes:

- Web: `/settings/server`
- Mobile: **Server** screen

Displays (read-only MVP):

| Section | Data source |
| ------- | ----------- |
| Docker | Container list / health from platform API |
| CPU / RAM | Host metrics (worker or backend probe) |
| GPU | Worker CUDA availability |
| Storage | Disk usage paths |
| Backups | Last backup timestamp (if configured) |
| Tailscale | Reachable / IP hint (host-reported) |
| Connected clients | Presence / mobile device registry |

API: `GET /api/shadow/mobile/server` (mobile) + shared platform health endpoints.

## Deployment profiles

See [DEPLOYMENT_PROFILES.md](DEPLOYMENT_PROFILES.md).

| Profile | Home server role |
| ------- | ---------------- |
| Personal Local | Dev machine, localhost only |
| Personal Remote | Home PC, Tailscale for remote clients |
| Home Server | NAS or dedicated box, multi-user tailnet |
| Cloud | Not home — future |

## Operations

- [PERSONAL_SERVER.md](../operations/PERSONAL_SERVER.md)
- [TAILSCALE_SETUP.md](../operations/TAILSCALE_SETUP.md)
- [DEPLOYMENT_GUIDE.md](../operations/DEPLOYMENT_GUIDE.md)

## Related

- [PERSONAL_REMOTE_ACCESS.md](PERSONAL_REMOTE_ACCESS.md)
- [SHADOW_EVERYWHERE.md](SHADOW_EVERYWHERE.md)
