# Shadow Everywhere — Architecture

Version: 1.1

Status: Active (Phase III)

Vision: [LUMEN_VISION_2030.md](../vision/LUMEN_VISION_2030.md)

Tasks: [TASK-0068](../../planning/Shadow/Sprint-68/TASK-0068.md) · [TASK-0069](../../planning/Shadow/Sprint-69/TASK-0069.md) · [TASK-0070](../../planning/Shadow/Sprint-70/TASK-0070.md)

---

# Purpose

**Shadow Everywhere** is the architectural chapter that lets Shadow exist on multiple surfaces while reusing one intelligence stack.

> **One Shadow. One Home. Everywhere.**

Clients are **thin presence surfaces**. Intelligence stays on the **home Lumen server** (Docker prod-like) for personal deployments.

---

# Layer stack

```text
Shadow Clients
  Desktop · Browser · Mobile · (IDE S71 · Ambient S72)
              │
              ▼
Transport Layer (opaque to Shadow)
  Localhost · LAN · Tailscale · Cloud (future)
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
Lumen Platform @ home          ← Docker, storage, models, Second Brain
```

**Home Server First:** Personal Remote via [Tailscale](TAILSCALE_ARCHITECTURE.md) is the recommended transport for developers and solo users before public cloud (Phase IV).

Deployment profiles: [DEPLOYMENT_PROFILES.md](DEPLOYMENT_PROFILES.md)

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

# Sprint map (Phase III)

| Sprint | Deliverable | Status |
| ------ | ----------- | ------ |
| 68 | Presence foundation, Tauri, Quick Launcher | ✅ |
| 69 | Browser Companion (MV3) | ✅ |
| **70** | **Mobile Companion + Personal Remote (Tailscale)** | **Planned** |
| 71 | IDE Companion | Planned |
| 72 | Ambient Shadow | Planned |

---

# Related documents

- [SHADOW_PRESENCE.md](SHADOW_PRESENCE.md)
- [CONTEXT_HUB.md](CONTEXT_HUB.md)
- [PRESENCE_SECURITY.md](PRESENCE_SECURITY.md)
- [DESKTOP_FOUNDATION.md](DESKTOP_FOUNDATION.md)
- [SHADOW_BROWSER.md](SHADOW_BROWSER.md)
- [SHADOW_MOBILE.md](SHADOW_MOBILE.md)
- [TAILSCALE_ARCHITECTURE.md](TAILSCALE_ARCHITECTURE.md)
- [PERSONAL_REMOTE_ACCESS.md](PERSONAL_REMOTE_ACCESS.md)
- [HOME_SERVER.md](HOME_SERVER.md)
- [Vision: Shadow Presence](../vision/SHADOW_PRESENCE.md)
