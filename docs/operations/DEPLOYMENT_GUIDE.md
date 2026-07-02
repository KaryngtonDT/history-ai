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
