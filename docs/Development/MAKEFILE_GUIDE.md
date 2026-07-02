# Makefile Guide

Lumen uses `make` as the executable documentation for daily operations.

```bash
make help
```

## Common workflows

### I changed backend code

```bash
make prod-backend
```

### I added a Doctrine migration

```bash
make prod-migrate
```

### I want to verify the whole platform

```bash
make ci
```

### I want production-like behaviour locally

```bash
make prod
make doctor
```

### I want a clean database but keep files

```bash
make prod-fresh
```

Requires typing `DELETE EVERYTHING`. Does **not** delete `./storage`.

### I want a full backup before a risky change

```bash
make backup
make verify-backup
```

## Environment variables

| Variable | Default | Purpose |
| -------- | ------- | ------- |
| `COMPOSE_FILE` | `docker-compose.prod-like.yml` | Compose file for prod commands |
| `API_BASE` | `http://localhost:8000` | Base URL for `doctor.sh` |

## Windows

Use WSL2 or Git Bash. PowerShell does not run the Makefile targets directly.
