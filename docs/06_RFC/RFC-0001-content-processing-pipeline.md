# RFC-0001 — Content Processing Pipeline

**Status:** Proposed

**Author:** History AI Team

**Created:** 2026-06-26

---

# 1. Problem Statement

History AI transforms educational content into structured learning material.

The platform must support many different input sources without changing the business workflow.

Examples:

* PDF
* Audio
* Video
* YouTube
* Website
* Book
* Presentation

The processing pipeline must remain identical regardless of the source.

---

# 2. Goals

The pipeline must:

* be source independent
* be AI provider independent
* be storage independent
* be horizontally scalable
* be observable
* be resumable
* be extensible

---

# 3. Non Goals

This RFC does not specify:

* UI
* Authentication
* Billing
* AI model selection
* Infrastructure technology

---

# 4. Core Domain

The business domain contains three primary aggregates.

## Content

Represents an educational resource.

Examples:

* Roman Empire.pdf
* History of Rome.mp3
* YouTube lecture
* PowerPoint presentation

Content never contains generated knowledge.

---

## ProcessingJob

Represents one execution inside the processing pipeline.

Examples:

* Import
* Transcription
* Translation
* Summary generation
* Quiz generation
* Podcast generation

Each ProcessingJob has its own lifecycle.

---

## Artifact

Represents generated knowledge.

Examples:

* Transcript
* Summary
* Timeline
* Quiz
* Flashcards
* Glossary
* Podcast
* Mind Map

Artifacts are immutable once completed.

---

# 5. Processing Pipeline

Every Content follows exactly the same lifecycle.

Content

↓

Validation

↓

Storage

↓

ProcessingJob

↓

Artifact Generation

↓

Learning Package

Only the implementation of each step changes.

The business flow never changes.

---

# 6. State Machine

Content

Draft

↓

Imported

↓

Processing

↓

Completed

↓

Archived

or

↓

Failed

---

ProcessingJob

Pending

↓

Running

↓

Completed

or

↓

Failed

or

↓

Cancelled

---

Artifact

Generating

↓

Ready

or

↓

Failed

---

# 7. Responsibilities

## Content

Owns:

* metadata
* source information
* lifecycle

Does not own:

* AI output

---

## ProcessingJob

Owns:

* execution
* progress
* retries
* errors
* worker assignment

Does not own:

* generated content

---

## Artifact

Owns:

* generated knowledge
* language
* version
* generation metadata

Does not own:

* execution

---

# 8. Architectural Principles

The domain must not depend on:

* Symfony
* Doctrine
* PostgreSQL
* Redis
* MinIO
* Whisper
* GPT
* Gemini

All external systems are infrastructure adapters.

---

# 9. Extensibility

Adding a new source must not modify the pipeline.

Example:

Today:

YouTube

Tomorrow:

Wikipedia

The processing flow remains identical.

---

Adding a new AI model must not modify the domain.

Today:

Whisper

Tomorrow:

Gemini Speech

Only infrastructure changes.

---

Adding a new Artifact must not modify Content.

Example:

Today:

Summary

Tomorrow:

Mind Map

Content remains unchanged.

---

# 10. Risks

Potential risks:

* oversized aggregates
* infrastructure leaking into domain
* synchronous processing
* AI vendor lock-in
* duplicated artifacts

These risks must be continuously monitored.

---

# 11. Future Evolution

Future RFCs will specify:

RFC-0002 — Content Model

RFC-0003 — ProcessingJob Lifecycle

RFC-0004 — Artifact Model

RFC-0005 — Storage Abstraction

RFC-0006 — Worker Architecture

RFC-0007 — AI Pipeline

RFC-0008 — Event Architecture

RFC-0009 — Learning Package

RFC-0010 — Multi-Agent Orchestration

---

# 12. Decision

History AI is defined as a content processing platform.

Its core business domain is based on three aggregates:

Content

↓

ProcessingJob

↓

Artifact

Every future feature must integrate into this model instead of introducing parallel workflows.
