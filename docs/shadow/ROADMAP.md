# Shadow — Roadmap

Version: 3.0

Status: Active

Constitutional reference: [LUMEN_VISION_2030.md](../vision/LUMEN_VISION_2030.md)

---

# Phases (summary)

| Phase | Name | Sprints | Status |
| ----- | ---- | ------- | ------ |
| I | Knowledge Processing | 31–52 | ✅ Complete |
| II | Shadow Intelligence | 55–67 | ✅ Complete |
| III | **Shadow Everywhere** | 68–72 | **Current** |
| IV | Learning Ecosystem | 73+ | Deferred |

---

# Phase II — Shadow Intelligence (complete)

| Sprint | Capability |
| ------ | ---------- |
| 55–57 | Shadow core, adaptive learning |
| 58 | Identity & Voice Studio |
| 60 | Session emotional intelligence |
| 61 | Relationship engine |
| 62 | Memory timeline & recall |
| 63 | Teaching engine |
| 64 | Knowledge graph & reasoning |
| 65 | Mentor & goal engine |
| 66 | Executive function & orchestration |
| **67** | **Second Brain & Knowledge Workspace** ✅ |

---

# Phase III — Shadow Everywhere (current)

> **One intelligence. Multiple points of presence.**

| Sprint | Name | Focus |
| ------ | ---- | ----- |
| **68** | **Shadow Everywhere Foundation** ✅ | Presence layer, Context Hub, universal conversation, Tauri foundation, Quick Launcher |
| **69** | **Browser Companion** ✅ | MV3 extension, Browser Presence, overlay, YouTube/reading, permissions |
| **70** | **Mobile Companion & Personal Remote Access** ← next | Flutter Android/iOS, Connection Manager, **Tailscale**, Today, Watch, Second Brain, server dashboard |
| 71 | IDE Companion | Cursor, VS Code, JetBrains — workspace-aware (with consent) |
| 72 | Ambient Shadow | Always-available presence, proactive missions (opt-in) |

### Sprint 70 highlights

**One product objective:** Shadow Mobile usable in daily life — home server first, no public cloud required.

| Slice | Focus |
| ----- | ----- |
| 01–02 | Mobile domain + Flutter foundation |
| 03–04 | Connection Manager + Tailscale / Auto LAN switching |
| 05–08 | Voice, Watch, Today, Second Brain |
| 09–11 | Notifications, Connections settings, Personal Server dashboard |
| 12 | Documentation |

**Deployment:** [Personal Remote](../../docs/architecture/DEPLOYMENT_PROFILES.md) (Docker + Tailscale) is the recommended profile.

See [TASK-0070](../../planning/Shadow/Sprint-70/TASK-0070.md).

### Sprint 68 highlights

**One product objective:** Shadow Presence foundation — not all platforms.

| Slice | Focus |
| ----- | ----- |
| 01–02 | Presence domain + Context Hub |
| 03 | Tauri foundation (auth, profile, windows) |
| 04 | Universal conversation |
| 05–06 | Presence settings + explainability/privacy |
| 07 | Desktop Quick Launcher (minimal) |
| 08 | Documentation |

**Deferred to S69–72:** Browser, IDE, Mobile, Ambient integrations.

See [TASK-0068](../../planning/Shadow/Sprint-68/TASK-0068.md).

---

# Phase IV — Learning Ecosystem (deferred)

Do **not** start until daily Shadow usage across companions is proven.

| Sprint | Capability |
| ------ | ---------- |
| 73 | Platform hardening |
| 74 | Public API |
| 75 | SDK |
| 76 | Marketplace |
| 77 | Enterprise |

---

# Obsolete roadmaps (do not follow)

| Superseded plan | Replaced by |
| --------------- | ----------- |
| S67–S72 "daily friction" (Desktop, Coding, Life Dashboard, Delight) | Phase III Shadow Everywhere (S68–72) |
| S67 platform hardening → S68 public API | Phase IV after companions |
| S73–74 Shadow Chronicle only | Chronicle may return inside Second Brain / Executive — not blocking Phase III |
| Web-first: Site → API → SDK | Shadow-first: Desktop → Browser → Mobile (Tailscale) → IDE |

---

# Documentation

- Vision: [LUMEN_VISION_2030.md](../vision/LUMEN_VISION_2030.md)
- Presence: [SHADOW_PRESENCE.md](../vision/SHADOW_PRESENCE.md)
- Continuity: [SHADOW_CONTINUITY.md](../vision/SHADOW_CONTINUITY.md)
- Surfaces: [MENTOR.md](MENTOR.md) · [EXECUTIVE.md](EXECUTIVE.md) · [SECOND_BRAIN.md](SECOND_BRAIN.md)
- Tasks: `planning/Shadow/Sprint-XX/TASK-00XX.md`
