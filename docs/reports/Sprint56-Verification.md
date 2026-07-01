# Sprint 56 — Shadow Proactive Tutor

**Date:** 2026-06-26  
**Status:** Complete

---

## Product goal

Deliver an **optional proactive tutor** for Shadow watch mode: deterministic intervention decisions, user-controlled policy, challenge/answer flow, and frontend UX that honors `recommendPause` / `recommendResume` without duplicating the video pipeline.

---

## Commits

| Slice | Message |
|-------|---------|
| 01 | `feat(shadow): add proactive tutor domain` |
| 02 | `feat(shadow): add proactive intervention engine` |
| 03 | `feat(shadow): integrate proactive conversation flow` |
| 04 | `feat(frontend): add proactive shadow tutor` |
| 05 | `docs(shadow): document proactive tutor mode` |

---

## CTO checklist

| Item | Status |
| ---- | ------ |
| Proactive mode off by default | ✅ `ShadowInterventionPolicy::disabled()` |
| User controls policy (toggle, frequency, challenge) | ✅ `PUT .../policy` + `ShadowTutorSettings` |
| Deterministic intervention decisions | ✅ `ShadowInterventionDecider` (no LLM for timing) |
| Backend recommends pause/resume only | ✅ `recommendPause`, `recommendResume` flags |
| Frontend controls video playback | ✅ `<video>` element |
| Manual Shadow Q&A still works | ✅ `POST .../ask` unchanged |
| Intervention answer uses LLM | ✅ `ShadowInterventionAnswerer` |
| UI localized en/fr/de | ✅ `pipeline.shadow.*` |
| No pipeline duplication | ✅ Reuses transcript/translation repos |
| OpenAPI documents new endpoints | ✅ Shadow proactive schemas |

---

## API endpoints

```
GET  /api/videos/{videoId}/shadow/sessions/{sessionId}/intervention?time=
POST /api/videos/{videoId}/shadow/sessions/{sessionId}/intervention/{interventionId}/answer
POST /api/videos/{videoId}/shadow/sessions/{sessionId}/intervention/{interventionId}/skip
PUT  /api/videos/{videoId}/shadow/sessions/{sessionId}/policy
```

OpenAPI schemas: `ShadowInterventionPolicy`, `ShadowIntervention`, `ShadowChallenge`, `ShadowInterventionCheck`, `ShadowInterventionAnswer`, `UpdateShadowInterventionPolicyRequest`.

---

## Validation

```bash
# Backend (Docker)
docker compose build backend
docker compose run --rm --entrypoint php backend bin/phpunit
docker compose run --rm --entrypoint php backend bin/phpunit tests/Architecture
docker compose run --rm --entrypoint php backend bin/phpunit tests/Functional/OpenApi

# Frontend
cd frontend && npm run build && npm test && npm run check

# Worker (if applicable)
pytest
ruff check .
```

---

## Roadmap alignment

| Sprint | Feature |
| ------ | ------- |
| **55** | Shadow AI Watch Companion ✅ |
| **56** | Shadow Proactive Tutor ✅ |
| **57** | Adaptive Intelligence |
| **58** | Public API |

---

## Documentation

- [SHADOW_PROACTIVE_TUTOR.md](../architecture/SHADOW_PROACTIVE_TUTOR.md)
- [SHADOW_WATCH_COMPANION.md](../architecture/SHADOW_WATCH_COMPANION.md)
- [openapi.md](../architecture/openapi.md) — Shadow proactive endpoints
