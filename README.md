# History AI

AI-powered platform that transforms educational content into structured learning experiences.

## Status

Milestone 1 — Project Foundation (in progress)

**Phase 2 — AI Video Localization Platform** started at Platform Sprint 31. Users can upload video files (mp4, mov, mkv) via `/video/upload`; jobs are transcribed via Faster-Whisper and transcripts are viewable at `/video/:videoId/transcript`. Multilingual translations are available at `/video/:videoId/translations` (Sprint 33). AI engine registry is viewable at `/settings/ai` (Sprint 34).

## Prerequisites

- Docker & Docker Compose
- Make (WSL2 or Git Bash on Windows)
- Git

## Getting started

1. Copy `.env.example` to `.env`
2. Read [START_HERE.md](START_HERE.md)
3. Run `make up` once infrastructure is configured

## Documentation

| Document | Purpose |
| -------- | ------- |
| [START_HERE.md](START_HERE.md) | Developer onboarding |
| [AGENTS.md](AGENTS.md) | AI assistant rules |
| [planning/WORKFLOW.md](planning/WORKFLOW.md) | Delivery workflow |
| [.ai/system/](.ai/system/) | Cursor system prompt and review checklists |
| [docs/](docs/) | Product and architecture |

## License

MIT — see [LICENSE](LICENSE).
