# Architecture Rules

Permanent rules for implementation. Source of truth: `docs/02_ARCHITECTURE/SYSTEM_BLUEPRINT.md`.

---

# Dependency Flow

```text
Frontend → REST API → Presentation → Application → Domain
Infrastructure → Application → Domain
Worker → pipeline → providers (no business rules in worker)
```

---

# Backend — Forbidden

* Domain → Symfony, Doctrine, HTTP, Redis
* Domain → another module's Domain
* Presentation → Infrastructure (direct)
* Presentation → Domain Entity (direct — use DTOs)

---

# Frontend — Forbidden

* `shared/` importing from `entities/`, `features/`, `pages/`
* Business logic in `pages/`
* Direct access to database, queue, or storage

---

# Worker — Forbidden

* Business rule decisions in worker
* Direct PostgreSQL access
* Provider → pipeline (reverse dependency)

---

# Cross-Cutting

* Secrets in source code
* Local filesystem as primary asset storage (use S3/MinIO)
* GraphQL, microservices, CQRS (MVP) unless ADR approves

---

# Module Boundaries (Backend)

| Module | Owns learning/content scope |
| ------ | --------------------------- |
| Content | Source import, VideoJob |
| Learning | LearningPackage, sessions |
| Knowledge | Summary, timeline, quiz, glossary |
| Tutor | AI tutoring |
| Library | Personal collection |
| User | Identity, settings |

Cross-module: Application services and ports only — never cross-Domain imports.

---

# When Rules Conflict

1. Task file (current work)
2. SYSTEM_BLUEPRINT.md
3. TECH_STACK.md
4. ADR in `engineering/ADR/`

If still ambiguous → ask the Software Architect.
