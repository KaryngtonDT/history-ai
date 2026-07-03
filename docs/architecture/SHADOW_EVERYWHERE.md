# Shadow Everywhere — Architecture

Version: 1.0

Status: Planned (Sprint 68)

Vision: [LUMEN_VISION_2030.md](../vision/LUMEN_VISION_2030.md)

Task: [TASK-0068](../../planning/Shadow/Sprint-68/TASK-0068.md)

---

# Purpose

**Shadow Everywhere** is the architectural chapter that lets Shadow exist on multiple surfaces while reusing one intelligence stack.

Sprint 68 delivers the **foundation layer only** — not Browser, IDE, or Mobile integrations.

> **One Shadow. Multiple Presences.**

---

# Layer stack

```text
Future surfaces (S69–S72)
  Browser · IDE · Mobile · Ambient
              │
              ▼
Desktop Quick Launcher (S68-SLICE-07) + Tauri foundation (S68-SLICE-03)
              │
              ▼
Shadow Presence Layer          ← Domain/ShadowPresence
              │
              ▼
Shadow Context Hub             ← Application/ShadowPresence
              │
              ▼
Shadow Intelligence (S55–S67) ← existing bounded contexts
              │
              ▼
Lumen Platform                 ← pipeline, storage, API
```

---

# Sprint 68 scope boundary

| In scope | Out of scope |
| -------- | ------------ |
| Presence domain & repository | Browser extension |
| Context Hub aggregation | IDE extension |
| Universal conversation bridge | Mobile apps |
| Presence settings (web) | Ambient / proactive voice |
| Explainability & audit log | Clipboard / page auto-read |
| Tauri shell + Quick Launcher | Duplicate Shadow engines |

---

# Related documents

- [SHADOW_PRESENCE.md](SHADOW_PRESENCE.md)
- [CONTEXT_HUB.md](CONTEXT_HUB.md)
- [PRESENCE_SECURITY.md](PRESENCE_SECURITY.md)
- [DESKTOP_FOUNDATION.md](DESKTOP_FOUNDATION.md)
- [Vision: Shadow Presence](../vision/SHADOW_PRESENCE.md)
