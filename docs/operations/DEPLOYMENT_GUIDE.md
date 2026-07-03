# Deployment Guide

## Prerequisites

- Docker & Docker Compose
- Make (WSL2 or Git Bash on Windows)
- 10 GB+ free disk (storage + models)

## First installation

```bash
cp .env.example .env
make install
```

## Production-like mode

```bash
make prod
make prod-migrate
make doctor
```

## Rebuild after code changes

```bash
make prod-rebuild          # all services
make prod-backend          # backend only
```

Data in `./storage`, `./models`, and named Docker volumes is preserved.

## Reset (destructive)

```bash
make prod-fresh            # removes postgres/redis/minio volumes only
```

`./storage` bind mount is **not** deleted by `prod-fresh`.

## Health endpoints

| Endpoint | Purpose |
| -------- | ------- |
| `GET /health` | Liveness |
| `GET /ready` | Readiness (postgres, storage, worker, …) |
| `GET /live` | Live + disk space |
| `GET /api/platform/readiness` | Production readiness score |

```bash
make health
make doctor
```

## Deployment profiles

Lumen supports multiple deployment profiles with **one codebase**. See [DEPLOYMENT_PROFILES.md](../architecture/DEPLOYMENT_PROFILES.md).

| Profile | Use when |
| ------- | -------- |
| **Personal Local** | Development on same machine (`localhost`) |
| **Personal Remote** ⭐ | Home Docker + phone/laptop away from home via **Tailscale** |
| **Home Server** | Family NAS / dedicated box (Tailscale) |
| **Cloud** | Future public API (Phase IV) |

For Personal Remote setup: [TAILSCALE_SETUP.md](TAILSCALE_SETUP.md) · [PERSONAL_SERVER.md](PERSONAL_SERVER.md)
