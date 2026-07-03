# Shadow Presence — Architecture

Version: 1.0

Status: Planned (Sprint 68)

Vision: [SHADOW_PRESENCE.md](../vision/SHADOW_PRESENCE.md)

Task: [TASK-0068](../../planning/Shadow/Sprint-68/TASK-0068.md)

---

# Bounded context

`Domain/ShadowPresence/` — describes **where** and **how authorized** Shadow appears, not surface-specific UI.

| Aggregate / VO | Role |
| -------------- | ---- |
| `PresenceSession` | Active connection from a surface |
| `PresenceContext` | Snapshot of fused intelligence for a request |
| `PresenceSource` | Enum: `Desktop`, `Browser`, `Ide`, `Mobile`, `Web` |
| `PresenceCapability` | What a surface can do (ask, search, resume mission) |
| `PresencePermission` | User-granted scope (duration, data types) |
| `PresenceAction` | User-initiated action from a surface |
| `PresenceIntent` | Parsed intent (explain, search, resume, …) |
| `PresenceState` | Connected, idle, disconnected |

Persistence: `storage/shadow/presence/{id}.json`

---

# Layer diagram

```text
Surface (Desktop / Browser / IDE / Web)
        │
        ▼
Presentation — ShadowPresenceController
        │
        ▼
Application — PresenceCoordinator, ContextHub
        │
        ├── ShadowSecondBrain (read)
        ├── ShadowMemory (read)
        ├── ShadowExecutive (read)
        ├── ShadowMentor (read)
        └── Shadow session / conversation (read/write bridge)
        │
        ▼
Domain — ShadowPresence
```

---

# API surface

See [TASK-0068](../../planning/Shadow/Sprint-68/TASK-0068.md) for endpoint list.

---

# Related

- [CONTEXT_HUB.md](CONTEXT_HUB.md)
- [PRESENCE_PERMISSIONS.md](PRESENCE_PERMISSIONS.md)
- [DESKTOP_FOUNDATION.md](DESKTOP_FOUNDATION.md)
- [PRIVACY_MODEL.md](PRIVACY_MODEL.md)
