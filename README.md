# History AI

AI-powered platform that transforms educational content into structured learning experiences.

## Status

Milestone 1 — Project Foundation (in progress)

**Phase 2 — AI Video Localization Platform** started at Platform Sprint 31. Users can upload video files (mp4, mov, mkv) via `/video/upload`; jobs are transcribed via Faster-Whisper and transcripts are viewable at `/video/:videoId/transcript`. Multilingual translations are available at `/video/:videoId/translations` (Sprint 33). AI engine registry is viewable at `/settings/ai` (Sprint 34). Translated audio can be generated and previewed at `/video/:videoId/audio` (Sprint 35). Voice cloning with OpenVoice V2 is available at `/video/:videoId/voice-clone` (Sprint 36). Lip-synced video preview is available at `/video/:videoId/lip-sync` (Sprint 37). Final rendered MP4 download is available at `/video/:videoId/render` (Sprint 38). Pipeline AI engine selection per stage is configurable at `/settings/pipeline` (Sprint 39). Automatic AI orchestration with manual/automatic processing mode is available on video upload (Sprint 40). AI Director smart video intelligence analyzes content and explains pipeline recommendations on upload (Sprint 41). Adaptive execution optimization tunes engine parameters automatically based on video intelligence (Sprint 42). Resource-aware pipeline scheduling exposes CPU/GPU/IO queue planning and progress monitoring (Sprint 43). Automatic quality assessment scores generated videos and recommends publication readiness (Sprint 44). Project workspace at `/workspace` organizes videos into projects and supports batch processing across multiple videos (Sprint 45). Execution history at `/workspace` tracks render versions, supports comparison, and enables reprocessing from any previous version (Sprint 46). User reviews and adaptive AI Director recommendations based on feedback preferences are available at `/workspace` (Sprint 47). Team collaboration with shared workspaces, role-based permissions, and member management is available at `/workspace` (Sprint 48).

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
