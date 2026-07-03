# Shadow Mobile Architecture

Version: 1.0

Status: Planned (Sprint 70)

## Principle

**One Shadow. One Home. Everywhere.**

The mobile app is a **presence client**, not an AI runtime. Speech recognition, synthesis, reasoning, memory, teaching, and Second Brain all execute on the **home Lumen server**.

## Client role

| Responsibility | Mobile | Lumen (home) |
| -------------- | :----: | :----------: |
| UI / navigation | ✅ | — |
| Microphone capture | ✅ | — |
| Audio playback | ✅ | — |
| Push notification display | ✅ | — |
| Connection selection | ✅ | — |
| STT / TTS / LLM | — | ✅ |
| Memory / Mentor / Executive | — | ✅ |
| Knowledge Graph | — | ✅ |
| Watch Companion logic | — | ✅ |

## Stack

- **Flutter** — single codebase for Android and iOS
- **Repository pattern** — `MobileRepository` → `HttpMobileRepository` (mirrors frontend conventions)
- **Connection Manager** — see [MOBILE_CONNECTION_MANAGER.md](MOBILE_CONNECTION_MANAGER.md)

## Layer diagram

```text
┌─────────────────────────────────────┐
│  Flutter UI (screens / widgets)     │
├─────────────────────────────────────┤
│  Services                           │
│  MobileSync · Health · Push · Voice │
├─────────────────────────────────────┤
│  Connection Manager                 │
│  Local · LAN · Tailscale · Auto     │
├─────────────────────────────────────┤
│  HTTP / WebSocket → Lumen API       │
└─────────────────────────────────────┘
              │
              ▼
     Shadow Presence (backend)
              │
              ▼
     Lumen Platform @ home
```

## Backend bounded context

`Domain/Mobile/` models:

- **MobileDevice** — device id, platform, capabilities
- **MobileSession** — active client session linked to Shadow presence
- **MobileConnection** — transport mode, endpoint, health snapshot
- **MobilePresence** — registration with global Shadow presence graph
- **MobileCapabilities** — voice, notifications, watch companion flags

Persistence: file-backed under `storage/shadow/mobile/` (consistent with Browser/Desktop sprints).

## API surface

See [TASK-0070](../../planning/Shadow/Sprint-70/TASK-0070.md) — `/api/shadow/mobile/*`.

## Screens (MVP)

| Screen | Function |
| ------ | -------- |
| Home | Status, today summary, voice CTA |
| Today | Missions, revisions, recommendations |
| Shadow Chat | Text continuity with Desktop/Browser |
| Shadow Voice | Voice-first session |
| Watch | Mobile entry to Watch Companion |
| Second Brain | Search concepts, sources, flashcards |
| Settings | Language, voice, notifications |
| Connections | Transport profile |
| Server | Read-only home server health |

## Continuity

Mobile registers via `POST /api/shadow/mobile/device` and syncs via `POST /api/shadow/mobile/sync`. Conversation and Second Brain state are **never** forked on-device.

## Related docs

- [SHADOW_EVERYWHERE.md](SHADOW_EVERYWHERE.md)
- [TAILSCALE_ARCHITECTURE.md](TAILSCALE_ARCHITECTURE.md)
- [PERSONAL_REMOTE_ACCESS.md](PERSONAL_REMOTE_ACCESS.md)
- [HOME_SERVER.md](HOME_SERVER.md)
