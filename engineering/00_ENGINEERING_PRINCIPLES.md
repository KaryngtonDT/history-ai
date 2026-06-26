# Engineering Constitution

Version: 1.0

Status: Active

---

# Purpose

This document defines the immutable engineering principles of History AI.

Architectures may evolve.

Frameworks may change.

Programming languages may change.

These principles must remain stable.

---

# Principle 1 — Business First

Technology exists to serve the business.

Business rules always have priority over technical convenience.

---

# Principle 2 — Domain Before Infrastructure

Infrastructure is replaceable.

The domain is not.

The business model must never depend on:

* Symfony
* Doctrine
* PostgreSQL
* Redis
* MinIO
* FastAPI
* React
* AI providers

---

# Principle 3 — Explicit Architecture

Architecture is never implicit.

Every major architectural decision must be documented.

Accepted mechanisms:

* RFC
* ADR

No undocumented architectural changes.

---

# Principle 4 — Testability

Every business rule must be testable.

Business rules must not require:

* HTTP
* Database
* Docker
* Redis
* External APIs

Unit tests validate the domain.

Integration tests validate infrastructure.

Functional tests validate the API.

---

# Principle 5 — Thin Controllers

Controllers translate protocols.

Controllers do not contain business logic.

Controllers do not access persistence directly.

Controllers orchestrate use cases only.

---

# Principle 6 — Application Orchestrates

Application services coordinate work.

They do not own business rules.

Business rules belong to the domain.

---

# Principle 7 — Rich Domain

The domain owns:

Validation.

Invariants.

Lifecycle.

Consistency.

Business decisions.

Never duplicate business rules outside the domain.

---

# Principle 8 — Infrastructure Adapters

Infrastructure implements ports.

Infrastructure never defines the business.

Every external dependency is replaceable.

---

# Principle 9 — Small Aggregates

Aggregates should remain focused.

Avoid oversized aggregates.

Prefer explicit collaboration between aggregates.

---

# Principle 10 — Quality Gates

No code is merged unless:

All tests pass.

No linter errors exist.

No formatter issues exist.

No static analysis errors exist.

No architecture violation exists.

---

# Principle 11 — Backward Compatibility

Public APIs evolve deliberately.

Breaking changes require:

RFC

Migration strategy

Versioning plan

---

# Principle 12 — Observability

Every asynchronous process must expose:

Status

Progress

Errors

Execution time

Retry information

Invisible systems are unacceptable.

---

# Principle 13 — Simplicity

Prefer:

Simple architecture.

Simple code.

Simple APIs.

Simple deployments.

Complexity requires explicit justification.

---

# Principle 14 — Documentation

Documentation is part of the product.

Code without documentation is incomplete.

Architecture without rationale is incomplete.

---

# Principle 15 — Continuous Evolution

Refactoring is expected.

Technical debt is tracked.

Architecture evolves intentionally.

Never by accident.

---

# Engineering Oath

Every engineer working on History AI agrees to protect:

The domain.

The product vision.

The architecture.

The learning experience.

Technology serves knowledge.

Never the opposite.
