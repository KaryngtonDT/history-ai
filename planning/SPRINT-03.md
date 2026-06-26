# Sprint 3 — Processing Domain

Status: **Planned**

Epic: **Epic 03 — Asynchronous Processing Pipeline**

RFC: [RFC-0001](../docs/06_RFC/RFC-0001-content-processing-pipeline.md)

---

# Sprint Goal

Introduce the **ProcessingJob** aggregate and a minimal async pipeline — no AI, no MinIO, no Artifacts.

At sprint end:

```text
PDF
  ↓
POST /contents          (existing)
  ↓
Content
  ↓
POST /contents/{id}/process
  ↓
ProcessingJob
  ↓
Worker (simulated — 2s tick)
  ↓
Completed
  ↓
Processing Page (real API)
```

---

# Method

Same discipline as Sprint 2:

- One slice at a time
- Backend domain before frontend wiring
- No UI redesign
- Simulated progress acceptable until real steps exist
- Tests required per slice

---

# Stories / Slices

| Slice | Story | Scope | Status |
| ----- | ----- | ----- | ------ |
| **S3-01** | ProcessingJob domain | Backend DDD, Doctrine, migration, tests | Planned |
| **S3-02** | Start processing API | `POST /api/contents/{id}/process` | Planned |
| **S3-03** | ProcessingStatus enum | Pending, Running, Completed, Failed | Planned |
| **S3-04** | Status API | `GET /api/processing/{id}` (simulated progress) | Planned |
| **S3-05** | Frontend wiring | Processing page → `HttpProcessingRepository` | Planned |
| **S3-06** | Worker | Python worker: Pending → Running → Completed | Planned |

Detail: [S3-SLICE-01.md](S3-SLICE-01.md) … [S3-SLICE-06.md](S3-SLICE-06.md)

---

# Explicitly Out of Scope

| Item | Target Sprint |
| ---- | ------------- |
| MinIO / file storage | Sprint 5 |
| Whisper / transcription | Sprint 6 |
| LLM / Summary / Quiz / Flashcards | Sprint 6 |
| Artifact aggregate | Sprint 4 |
| Real processing steps (Extract, Summarize, …) | Sprint 4+ |
| Authentication | Later |

---

# Architecture Target

## Backend (new bounded context)

```text
Domain/Processing/
  ProcessingJob.php          (aggregate)
  ProcessingJobId.php
  ProcessingStatus.php       (Pending | Running | Completed | Failed)
  ProcessingJobRepositoryInterface.php

Application/Processing/
  Commands/StartProcessingCommand.php
  Queries/GetProcessingStatusQuery.php
  Handlers/...

Infrastructure/Persistence/Doctrine/Processing/
  ProcessingJobRecord.php
  DoctrineProcessingJobRepository.php

Presentation/Http/Controller/
  StartProcessingController.php
  GetProcessingStatusController.php
```

## Frontend (mirror Content pattern)

```text
services/processing/
  api/ProcessingApiDto.ts
  domain/Processing.ts
  mappers/ProcessingMapper.ts
  ProcessingRepository.ts
  MockProcessingRepository.ts      ← remove after S3-05
  HttpProcessingRepository.ts
  ProcessingRepositoryFactory.ts
  ProcessingService.ts
```

## Worker

```text
worker/
  poll_processing_jobs.py    (or Symfony Messenger consumer — TBD in S3-06)
```

Simple loop: pick Pending jobs → set Running → wait 2s → set Completed.

---

# API Contracts (preview)

## POST /api/contents/{id}/process

Creates a `ProcessingJob`. Does **not** execute processing inline.

Response `201`:

```json
{ "processingJobId": "uuid" }
```

## GET /api/processing/{id}

Response `200`:

```json
{
  "status": "running",
  "progress": 42,
  "currentStep": "Extracting text"
}
```

Progress and `currentStep` are **simulated** in Sprint 3.

---

# Future Sprints (roadmap)

| Sprint | Focus |
| ------ | ----- |
| **4** | Artifact domain + Summary |
| **5** | MinIO + real PDF upload |
| **6** | AI pipeline (Whisper, LLM, Quiz, Flashcards, Podcast, …) |

---

# Cursor Prompt (per slice)

```text
Read AGENTS.md.
Read START_HERE.md.
Read docs/00_PROJECT/PRODUCT_MANIFESTO.md.
Read engineering/00_ENGINEERING_PRINCIPLES.md.
Read docs/06_RFC/RFC-0001-content-processing-pipeline.md.
Read planning/SPRINT-03.md.
Implement Sprint 3 — Slice XX: [title].

Do not introduce AI, MinIO, or Artifact.
Do not modify UI layout.
Keep build, tests, and Biome checks green.
```
