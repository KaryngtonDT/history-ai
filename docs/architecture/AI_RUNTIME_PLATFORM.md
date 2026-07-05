# AI Runtime Platform

Version: 1.0

Status: Active

---

## Purpose

Lumen's **AI Runtime Platform** is the single source of truth for which engines are configured, discovered, ready, benchmarked, and executed.

Principle: **Configured. Verified. Measured. Intelligent. Explainable.**

## Architecture

```text
Presentation  →  /api/runtime/*
Application   →  RuntimePlatformInterface
Infrastructure→  Discovery · Readiness · Health · Benchmark · Intelligence
Domain        →  Runtime* · Engine*
```

## Deployment

| Layer | Components |
| ----- | ---------- |
| Docker | Frontend, Backend, Worker, PostgreSQL, Redis |
| Host | Ollama, Faster Whisper, F5-TTS, OpenVoice, LatentSync, FFmpeg |
| Volumes | `models/` (weights), `storage/` (data + runtime state) |

## API

Base path: `/api/runtime`

See OpenAPI tag **Runtime** and `planning/Platform/Sprint-70.4/TASK-0070.4.md`.

## UI

Route: `/settings/runtime` — Runtime Center dashboard.
