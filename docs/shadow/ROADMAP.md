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
| **68** | **Shadow Everywhere Foundation** | Presence domain, Context Hub, Desktop MVP (Tauri), Quick Assist |
| 69 | Browser Companion | Chrome, Firefox, Edge, Safari — YouTube, PDF overlays |
| 70 | IDE Companion | Cursor, VS Code, JetBrains — workspace-aware (with consent) |
| 71 | Mobile Companion | Android, iOS — continuity, daily review, voice |
| 72 | Ambient Shadow | Always-available presence, proactive missions (opt-in) |

### Sprint 68 highlights

- `ShadowPresence` bounded context — session, context, permissions, intents
- **Context Hub** — fuses Watch, Memory, Mentor, Executive, Second Brain into one context
- **Desktop Companion** (Tauri) — ultra-light, < 300 ms, not a Lumen clone
- **Quick Assist** — global shortcut, search, concept lookup, mission resume
- **Universal Conversation** — same thread across web, desktop, future surfaces
- **Presence Settings** — `/settings/shadow/presence`
- **Explainability** — every appearance has a documented *why*

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
| Web-first: Site → API → SDK | Shadow-first: Desktop → Browser → IDE → Mobile |

---

# Documentation

- Vision: [LUMEN_VISION_2030.md](../vision/LUMEN_VISION_2030.md)
- Presence: [SHADOW_PRESENCE.md](../vision/SHADOW_PRESENCE.md)
- Continuity: [SHADOW_CONTINUITY.md](../vision/SHADOW_CONTINUITY.md)
- Surfaces: [MENTOR.md](MENTOR.md) · [EXECUTIVE.md](EXECUTIVE.md) · [SECOND_BRAIN.md](SECOND_BRAIN.md)
- Tasks: `planning/Shadow/Sprint-XX/TASK-00XX.md`
