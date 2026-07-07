# Continuous Integration

Version: 1.0

Status: Active

---

# Purpose

GitHub Actions runs automated validation on every **push** and **pull request** to catch regressions before merge.

No deployment, Docker publishing, or release steps are included in this pipeline.

Related: [architecture-rules.md](./architecture-rules.md)

---

# Pipeline overview

```text
Push / Pull Request
        │
        ▼
   GitHub Actions (ci.yml)
        │
        ├── Backend
        │      ├── Composer install
        │      ├── PHPUnit (full suite)
        │      └── Architecture tests
        │
        ├── Frontend
        │      ├── npm ci
        │      ├── npm run build
        │      ├── npm test (incl. architecture)
        │      └── npm run check (Biome)
        │
        └── Worker
               ├── uv sync
               ├── pytest (incl. architecture)
               └── ruff check
```

Jobs run **in parallel**. Each job fails fast: the first failing step stops that job.

`concurrency` cancels in-progress runs for the same branch when a newer commit is pushed.

---

# Workflow file

| File | Role |
| ---- | ---- |
| `.github/workflows/ci.yml` | Main CI pipeline |

---

# Job details

## Backend

| Step | Command | Notes |
| ---- | ------- | ----- |
| Install | `composer install` | Cached via `backend/vendor` + `composer.lock` hash |
| PHPUnit | `php bin/phpunit` | SQLite in-memory (`phpunit.dist.xml`); no Postgres required; **fails on any deprecation or notice** (PHP and PHPUnit) |
| Architecture | `composer architecture` | Layer dependency rules (`tests/Architecture/`) |

**Runtime:** PHP 8.4, extensions `ctype`, `iconv`, `intl`, `pdo_sqlite`.

## Frontend

| Step | Command | Notes |
| ---- | ------- | ----- |
| Install | `npm ci` | Cached via `actions/setup-node` + `package-lock.json` |
| Build | `npm run build` | Production build validation (`tsc` + Vite) |
| Test | `npm test` | Vitest; includes `src/architecture/` rules |
| Lint | `npm run check` | Biome format + lint |

**Runtime:** Node.js 22.

**Environment:**

| Variable | Build | Test |
| -------- | ----- | ---- |
| `VITE_USE_MOCK` | `false` | `true` |
| `VITE_API_BASE_URL` | `http://localhost:8000` | (default from vitest config) |

## Worker

| Step | Command | Notes |
| ---- | ------- | ----- |
| Install | `uv sync --frozen --group dev` | Cached via `setup-uv` + `uv.lock` |
| Test | `uv run pytest` | Includes `tests/test_architecture.py` |
| Lint | `uv run ruff check .` | Python style and import rules |

**Runtime:** Python 3.13.

---

# GitHub secrets

**None required** for this pipeline.

Tests use in-memory SQLite (backend), mock repositories (frontend Vitest), and mock AI providers (worker). No API keys, registry credentials, or cloud access are needed for CI.

Optional secrets for future workflows (not used today):

| Secret | Future use |
| ------ | ---------- |
| `GEMINI_API_KEY` | Live AI smoke tests (manual / scheduled only) |
| `DOCKER_*` | Image publishing (explicitly out of scope) |

---

# Run locally

Equivalent commands (Docker Compose dev stack not required):

```bash
# Backend
cd backend
composer install
php bin/phpunit
composer architecture

# Frontend
cd frontend
npm ci
npm run build
npm test
npm run check

# Worker
cd worker
uv sync --frozen --group dev
uv run pytest
uv run ruff check .
```

With Docker Compose (matches local dev):

```bash
docker compose exec backend php bin/phpunit
docker compose exec backend composer architecture
docker compose exec frontend npm run build
docker compose exec frontend npm test
docker compose exec frontend npm run check
docker compose exec worker pytest
docker compose exec worker ruff check .
```

---

# Failure diagnosis

| Failed job | Likely cause | What to check |
| ---------- | ------------ | ------------- |
| Backend PHPUnit | Business logic, functional test regression, or PHPUnit deprecation/notice | Failing test class name in log; mock without `expects()` triggers notice |
| Backend Architecture | Forbidden layer import (e.g. Doctrine in Domain) | [architecture-rules.md](./architecture-rules.md) |
| Frontend Build | TypeScript or Vite compile error | `npm run build` output locally |
| Frontend Test | Component or architecture rule violation | Test file path in Vitest output |
| Frontend Biome | Format or lint drift | `npm run check` locally; run Biome fix if configured |
| Worker Pytest | Processing or architecture test failure | `tests/test_architecture.py` for dependency violations |
| Worker Ruff | Python lint (imports, style) | `uv run ruff check .` locally |

Architecture tests are also documented in [architecture-rules.md](./architecture-rules.md).

---

# Out of scope (Sprint 11 Slice 3)

- Deployment to staging or production
- Docker image build or push
- Release tagging or changelog automation
- Code coverage upload
- E2E tests against a live stack

These may be added in later slices.
