# SYSTEM BLUEPRINT

Version: 1.0

Status: Approved — Architecture frozen

---

# Role

This document is the **architectural bridge** between system design and code.

It defines:

* repository layout ;
* module boundaries ;
* module responsibilities ;
* allowed and forbidden dependencies ;
* placement conventions.

**Documentation hierarchy:**

```text
Vision
  ↓
Product
  ↓
Architecture (docs/02_ARCHITECTURE/)
  ↓
System Blueprint (this document) ⭐
  ↓
Engineering (engineering/)
  ↓
Implementation (Sprint / Work Packages)
```

**Companion documents:**

| Document | Role |
| -------- | ---- |
| `TECH_STACK.md` | What technologies we use |
| `SYSTEM_BLUEPRINT.md` | How the system is organized |
| `engineering/ADR/` | Why choices were made |

Once this document is **Approved**, architecture documentation is **frozen**. Further architectural changes require an ADR.

---

# 1. Repository Layout

```text
history-ai/
│
├── AGENTS.md
├── START_HERE.md
├── README.md
├── LICENSE
├── Makefile
├── .editorconfig
├── .gitignore
├── .env.example
│
├── docs/
├── engineering/
├── planning/
│
├── backend/
├── frontend/
├── worker/
│
├── infrastructure/
│
└── .github/
    └── workflows/
```

| Path | Role |
| ---- | ---- |
| `backend/` | Modular monolith — business rules and API |
| `frontend/` | React application — Feature-Sliced Design |
| `worker/` | Async processing — pipelines and providers only |
| `infrastructure/` | Docker, scripts, compose, monitoring |
| `docs/` | Product and architecture specifications |
| `engineering/` | Standards and ADRs |
| `planning/` | Sprint and work package plans |

---

# 2. Backend Blueprint

## Module Tree

```text
backend/
│
├── config/
├── migrations/
├── public/
├── tests/
└── src/
    │
    ├── Content/
    ├── Learning/
    ├── Knowledge/
    ├── Tutor/
    ├── Library/
    ├── User/
    │
    ├── Shared/
    ├── Infrastructure/
    └── Presentation/
```

Each business module contains:

```text
{Module}/
├── Domain/
└── Application/
```

`Infrastructure/` and `Presentation/` are shared technical layers.

---

## Content

**Responsibility**

Import and lifecycle of Source Content. Bridge between external sources and the Learning Pipeline.

**Owns**

* SourceContent
* ContentImport
* VideoJob (technical processing record — invisible to learners)

**Uses**

* Infrastructure (storage, queue)
* User (ownership, when authenticated)

**Cannot access**

* Tutor
* Billing (future)

---

## Learning

**Responsibility**

Generate and manage Learning Packages. Orchestrate the learning experience around a package.

**Owns**

* LearningPackage
* LearningSession
* Revision
* VoiceProfile

**Uses**

* Content (source reference)
* Knowledge (extracted assets)
* Infrastructure (storage, queue, messaging)

**Cannot access**

* Tutor (direct domain access)
* Billing (future)

---

## Knowledge

**Responsibility**

Knowledge extraction and structured educational artifacts.

**Owns**

* Summary
* Timeline
* Glossary
* Flashcards
* Quiz
* KnowledgeGraph (future)

**Uses**

* Content (source material)
* Infrastructure (AI pipeline ports, storage)

**Cannot access**

* Library (direct)
* Tutor
* Billing (future)

---

## Tutor

**Responsibility**

Interactive AI tutoring grounded in generated Knowledge only.

**Owns**

* TutorConversation
* TutorMessage
* TutorContext

**Uses**

* Knowledge (retrieval boundary)
* Learning (session context)
* Infrastructure (AI providers)

**Cannot access**

* Content (raw source)
* Billing (future)

**Domain rule**

AI Tutor never answers outside generated Knowledge.

---

## Library

**Responsibility**

Learner's personal collection of Learning Packages.

**Owns**

* Library
* Collection (future)
* Bookmark (future)

**Uses**

* Learning (LearningPackage reference)
* User (ownership)

**Cannot access**

* Knowledge (direct extraction logic)
* Content (import logic)
* Billing (future)

---

## User

**Responsibility**

Learner identity, authentication, and personal settings.

**Owns**

* User (maps to domain term **Learner** in product language)
* Account
* Settings

**Uses**

* Infrastructure (persistence, security)

**Cannot access**

* Knowledge
* Tutor
* Learning (direct package generation)
* Billing (future — except own subscription read model later)

**Naming rule**

Product docs say **Learner**. Code module is **User** for conventional auth boundaries. API responses use domain language where specified in `DOMAIN_MODEL.md`.

---

## Shared

**Responsibility**

Cross-module primitives with no business ownership.

**Owns**

* Shared value objects
* Shared exceptions
* Kernel

**Uses**

* Nothing domain-specific

**Cannot access**

* Any business module Domain layer

---

## Infrastructure

**Responsibility**

Technical adapters — persistence, messaging, storage, external APIs.

**Owns**

* Doctrine mappings
* Repository implementations
* Messenger handlers (transport)
* S3 / MinIO adapters
* Redis adapters
* External API clients

**Uses**

* Application ports from all modules

**Cannot access**

* Presentation
* Frontend

---

## Presentation

**Responsibility**

HTTP API — REST JSON for MVP.

**Owns**

* Controllers
* Request / Response objects
* API serialization

**Uses**

* Application layer from all modules

**Cannot access**

* Infrastructure (directly)
* Domain entities (directly — use Application DTOs)

---

# 3. Frontend Blueprint

Feature-Sliced Design (FSD). Aligns with backend bounded contexts via `entities/` and `features/`.

## Module Tree

```text
frontend/
└── src/
    │
    ├── app/
    ├── pages/
    ├── widgets/
    ├── features/
    ├── entities/
    └── shared/
```

## Layer Responsibilities

| Layer | Responsibility |
| ----- | -------------- |
| `app/` | Bootstrap, providers, router, global styles |
| `pages/` | Route composition — no business logic |
| `widgets/` | Composite UI blocks (dashboard, player panel) |
| `features/` | User actions (submit URL, download, start quiz) |
| `entities/` | Business nouns — types, API hooks, small UI |
| `shared/` | UI kit, HTTP client, lib, config |

## Entity Mapping (MVP)

| Entity slice | Backend module |
| ------------ | -------------- |
| `learning-package/` | Learning |
| `video-job/` | Content (technical status UI) |
| `knowledge/` | Knowledge |
| `library/` | Library |
| `user/` | User |

## FSD Dependency Rule

```text
app
  ↓
pages
  ↓
widgets
  ↓
features
  ↓
entities
  ↓
shared
```

| Layer | May import | Must not import |
| ----- | ---------- | --------------- |
| `shared/` | — | entities, features, widgets, pages |
| `entities/` | shared | features, widgets, pages |
| `features/` | entities, shared | widgets, pages, sibling features |
| `widgets/` | features, entities, shared | pages |
| `pages/` | all below | — |
| `app/` | all | — |

Frontend communicates with backend **only via REST API**. No direct database, queue, or storage access.

---

# 4. Worker Blueprint

The worker performs **processing only**. No business rules. No domain model.

Business rules live in the backend. The worker executes pipeline steps and reports results.

## Module Tree

```text
worker/
└── app/
    │
    ├── pipeline/
    ├── providers/
    ├── workers/
    └── shared/
```

| Folder | Responsibility |
| ------ | -------------- |
| `pipeline/` | Ordered processing steps (download, transcribe, translate, …) |
| `providers/` | External system adapters (S3, OpenAI, yt-dlp, Whisper) |
| `workers/` | Queue consumers — entry points |
| `shared/` | Config, logging, HTTP client to backend API |

## Worker Rules

* No business vocabulary in worker code — use IDs and payloads defined by backend contracts
* No direct PostgreSQL access from worker (backend owns state)
* Worker reads/writes objects via S3 and reports status via backend API or queue messages
* Pipeline steps do not call each other directly — orchestrator only

---

# 5. Infrastructure Blueprint

```text
infrastructure/
│
├── docker/
│   ├── backend/
│   ├── frontend/
│   └── worker/
│
├── compose/
│   └── (compose overrides, profiles)
│
├── scripts/
│   └── (init, seed, utility)
│
└── monitoring/
    └── (future — metrics, dashboards)
```

| Folder | Responsibility |
| ------ | -------------- |
| `docker/` | Dockerfiles per service |
| `compose/` | Compose fragments and environment-specific overrides |
| `scripts/` | Init scripts (MinIO bucket, etc.) |
| `monitoring/` | Observability config (future) |

Root `docker-compose.yml` orchestrates local development. Production compose lives under `infrastructure/compose/` when needed.

---

# 6. Allowed Dependencies

## Global Flow

```text
Frontend
  ↓ REST
Presentation
  ↓
Application
  ↓
Domain
  ↑
Infrastructure (implements Application ports)
```

## Backend

```text
Presentation  →  Application  →  Domain
Infrastructure  →  Application  →  Domain
Shared  →  used by all (no upward dependency)
```

Cross-module communication:

```text
Module A Application  →  Module B Application (via explicit port / facade)
Module A Domain  →  never imports Module B Domain
```

Prefer domain events or application services over direct cross-module entity access.

## Frontend

```text
pages  →  widgets  →  features  →  entities  →  shared
```

## Worker

```text
workers  →  pipeline  →  providers
workers  →  shared
```

## System

```text
Frontend  →  Backend API  →  Application  →  Domain
Backend  →  Queue  →  Worker  →  Providers  →  External Services
Backend / Worker  →  Object Storage (S3 / MinIO)
Backend  →  PostgreSQL
Backend  →  Redis
```

---

# 7. Forbidden Dependencies

## Backend

❌ Domain → Symfony

❌ Domain → Doctrine

❌ Domain → HTTP

❌ Domain → Redis

❌ Domain → another module's Domain

❌ Application → Presentation

❌ Presentation → Infrastructure (direct)

❌ Presentation → Domain Entity (direct — use DTOs)

## Frontend

❌ entities → features

❌ shared → entities

❌ Direct database or queue access

❌ Business logic in `pages/`

## Worker

❌ Worker → Domain model (PHP)

❌ Worker → PostgreSQL (direct)

❌ Provider → pipeline

❌ Pipeline step → pipeline step (direct — use orchestrator)

❌ Business rule decisions in worker

## Cross-system

❌ Domain → React

❌ Frontend → MinIO (direct)

❌ Secrets in source code

---

# 8. Module Responsibility Matrix

| Module | Responsibility | Owns | Uses | Cannot access |
| ------ | -------------- | ---- | ---- | ------------- |
| Content | Source import & processing job | SourceContent, VideoJob | Infrastructure, User | Tutor, Billing |
| Learning | Learning packages & sessions | LearningPackage, LearningSession, Revision | Content, Knowledge, Infrastructure | Tutor, Billing |
| Knowledge | Knowledge extraction | Summary, Timeline, Glossary, Flashcards, Quiz | Content, Infrastructure | Library, Tutor, Billing |
| Tutor | AI tutoring | TutorConversation, TutorMessage | Knowledge, Learning, Infrastructure | Content, Billing |
| Library | Personal collection | Library | Learning, User | Knowledge, Content, Billing |
| User | Identity & settings | User, Account, Settings | Infrastructure | Knowledge, Tutor, Learning |
| Shared | Shared primitives | Kernel, shared types | — | All Domain modules |
| Infrastructure | Technical adapters | Persistence, messaging, storage | Application ports | Presentation |
| Presentation | REST API | Controllers, requests | Application | Infrastructure, Domain direct |

---

# 9. Object Storage Layout

All generated assets — backend and worker — use S3-compatible paths:

```text
s3://{bucket}/jobs/{jobId}/
    source_audio/
    transcript/
    translation/
    analysis/
    audio/
    exports/
    logs/
```

No persistent business files in repository root.

Temporary worker cache: `worker/tmp/` — gitignored.

---

# 10. Placement Conventions

## Backend

| Artifact | Location |
| -------- | -------- |
| Entity | `src/{Module}/Domain/Entity/` |
| Value Object | `src/{Module}/Domain/ValueObject/` |
| Repository interface | `src/{Module}/Domain/Repository/` |
| Command / Handler | `src/{Module}/Application/` |
| Doctrine mapping | `src/Infrastructure/Persistence/{Module}/` |
| REST controller | `src/Presentation/Controller/Api/` |
| Migration | `migrations/` |

## Frontend

| Artifact | Location |
| -------- | -------- |
| Page | `src/pages/{name}/` |
| Feature | `src/features/{name}/` |
| Entity slice | `src/entities/{name}/` |
| Shared UI | `src/shared/ui/` |
| API client | `src/shared/api/` |

## Worker

| Artifact | Location |
| -------- | -------- |
| Pipeline step | `app/pipeline/{name}/steps/` |
| Orchestrator | `app/pipeline/{name}/orchestrator.py` |
| Consumer | `app/workers/{name}/` |
| External adapter | `app/providers/{name}/` |

---

# 11. Adding a New Module

1. CTO approval required.
2. Update this document first.
3. Create ADR if the change is architectural.
4. Add module folder structure before writing code.
5. Define Responsibility / Owns / Uses / Cannot access.

---

# 12. Approval Gate

| Status | Meaning |
| ------ | ------- |
| Draft | Under review — no code generation |
| Approved | Frozen — changes require ADR |

Sprint-00 starts only when:

* `TECH_STACK.md` — **Approved**
* `SYSTEM_BLUEPRINT.md` — **Approved**
* ADR-0001 through ADR-0005 written
