# Shadow Presence — Architecture

Version: 1.1

Status: Planned (Sprint 68)

Parent: [SHADOW_EVERYWHERE.md](SHADOW_EVERYWHERE.md)

Vision: [SHADOW_PRESENCE.md](../vision/SHADOW_PRESENCE.md)

Task: [TASK-0068](../../planning/Shadow/Sprint-68/TASK-0068.md)

---

# Bounded context

`Domain/ShadowPresence/` — **where**, **what authorized**, **which context** — never surface UI.

| Type | Members |
| ---- | ------- |
| Aggregates / entities | `PresenceSession`, `PresenceContext` |
| Value objects | `PresenceSurface`, `PresenceCapability`, `PresencePermission`, `PresenceState`, `PresenceEvent` |
| Port | `ShadowPresenceRepositoryInterface` |

Persistence: `storage/shadow/presence/{id}.json`

---

# Surfaces (enum)

| Value | Sprint |
| ----- | ------ |
| `web` | existing Lumen |
| `desktop` | S68 foundation + Quick Launcher |
| `browser` | S69 stub in settings |
| `ide` | S70 stub in settings |
| `mobile` | S71 stub in settings |

---

# Layer diagram

```text
Surface (web / desktop / future)
        │
        ▼
ShadowPresenceController
        │
        ▼
PresenceCoordinator · PresenceSessionManager
        │
        ▼
ContextHub → existing Shadow handlers (read)
        │
        ▼
Domain/ShadowPresence
```

---

# Related

- [CONTEXT_HUB.md](CONTEXT_HUB.md)
- [PRESENCE_SECURITY.md](PRESENCE_SECURITY.md)
- [DESKTOP_FOUNDATION.md](DESKTOP_FOUNDATION.md)
