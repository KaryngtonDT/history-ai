# Sprint 2 — Architecture Review

Status: **Approved**

Date: 2026-06-26

Reviewer lens: Tech Lead / Senior Team

---

# Executive Summary

Sprint 2 successfully transitioned the frontend from a clickable MVP (mock-by-screen) to a **domain-aligned, API-connected** application. The Content aggregate is complete end-to-end. The primary gap is the **business pipeline** — not structural quality.

**Recommendation:** Do not refactor further. Begin Sprint 3 on `ProcessingJob`.

---

# Scorecard

| Domaine | Note | Commentaire |
| ------- | ---- | ----------- |
| Architecture Backend | **9.5 / 10** | DDD + CQRS clean; Content aggregate production-ready |
| Architecture Frontend | **9 / 10** | Repository pattern, config, errors, mappers — S2-06 solidified |
| Documentation | **10 / 10** | Engineering, RFC, frontend architecture docs |
| Tests | **9 / 10** | 46 frontend + backend functional tests; mapper coverage strong |
| Découplage | **9.5 / 10** | Features → Services → Repositories → HttpClient |
| Évolutivité | **9.5 / 10** | Ready for ProcessingJob without rework |

**Missing for 10/10:** Processing pipeline (`ProcessingJob` → `Artifact` → AI).

---

# Backend Review

## Strengths

- Clear bounded context: `Domain/Content/` with value objects and aggregate root
- CQRS handlers separated from HTTP layer
- Doctrine mapping isolated in Infrastructure
- Functional tests on API endpoints
- Migration discipline

## Current boundary

```text
Content (complete)
ProcessingJob (not started)
Artifact (not started)
```

Aligns with RFC-0001 three-aggregate model.

## No action required

Backend is frozen for Sprint 2 scope. Sprint 3 adds a new bounded context — no Content refactor.

---

# Frontend Review

## Strengths

- Single `ContentService` replaces screen-based services
- `HttpClient` is the only fetch gateway
- `ContentMapper` owns API ↔ domain translation
- Centralized config (`env.ts` → `features.ts`, `api.ts`)
- Typed error hierarchy propagates cleanly
- PR quality checklist for future slices

## Remaining mock surface

| Area | State | Sprint |
| ---- | ----- | ------ |
| Processing page | `MockProcessingRepository` + client simulation | S3 |
| Upload progress | UI-only timer | S5 (MinIO) |
| Dashboard artifacts count | Hardcoded placeholder | S4 |

## No action required

S2-06 cleanup is complete. Processing domain follows same pattern in S3.

---

# Integration Review

## Working flows

```text
Import  → POST /api/contents → PostgreSQL
Library → GET  /api/contents → PostgreSQL
Dashboard → GET /api/contents → computeStatistics()
```

## Docker

- Frontend built with `VITE_USE_MOCK=false`
- Backend on `:8000`, frontend on `:5173`
- CORS subscriber active in dev

## Known drift

- Frontend `sourceType: "pdf"` ↔ API `upload_pdf` — mapped in `ContentMapper`; documented as tech debt

---

# Domain Model Progression (RFC-0001)

```text
Sprint 2 ✅  Content
Sprint 3     ProcessingJob + Worker (simulated)
Sprint 4     Artifact (Summary)
Sprint 5     MinIO + real file upload
Sprint 6     AI capabilities (Whisper, LLM, Quiz, …)
```

This ordering maximizes product value: **workflow first, intelligence second**.

---

# Risks Identified

| Risk | Mitigation (Sprint 3) |
| ---- | --------------------- |
| Processing page still client-simulated | S3-SLICE-05 wires to real API |
| No async job infrastructure | S3-SLICE-06 introduces Worker (simple poll/tick) |
| Content status not updated by pipeline | Defer to S3 — ProcessingJob has own lifecycle |
| Over-engineering Worker | S3 Worker: Pending → Running → Completed every 2s, no AI |

---

# Approval

Sprint 2 is **closed**. Architecture is stable. Proceed to Sprint 3.

Signed-off criteria met:

- [x] All 6 slices complete
- [x] Build, tests, Biome green
- [x] No new features in S2-06
- [x] Documentation updated
- [x] Explicit deferral list for MinIO / AI / Artifacts
