# Shadow Presence

Version: 1.0

Status: Approved (concept)

Parent: [LUMEN_VISION_2030.md](LUMEN_VISION_2030.md)

---

# Definition

**Shadow Presence** is the ability for Shadow to appear consistently across surfaces — desktop, browser, IDE, mobile — without the user opening Lumen first.

The user does not launch Shadow.

Shadow is already there when learning happens.

---

# Interaction model

```text
User selects text / invokes shortcut / taps icon
        │
        ▼
Shadow Presence Layer (surface-specific UI)
        │
        ▼
Shadow Context Hub (unified context)
        │
        ▼
Shadow Intelligence (Memory, Brain, Mentor, …)
        │
        ▼
Second Brain enriched
```

---

# Signature UX

Select `Dependency Injection` anywhere → discrete halo → **Shadow** → in one second:

- what this concept is
- where you learned it
- exposure count and errors
- linked videos and exercises
- active missions

Same panel. Same voice. Same memory. Every surface.

---

# Design constraints

| Constraint | Rule |
| ---------- | ---- |
| Discrete | Never interrupt without permission or explicit proactive setting |
| Lightweight | Desktop companion opens in < 300 ms (Raycast / Spotlight class) |
| Explainable | Every appearance has a *why* |
| Privacy | No clipboard, code, or document access without explicit user action |
| Unified | One Shadow identity — never multiple personas per surface |

---

# Surfaces (roadmap)

| Sprint | Surface | Role |
| ------ | ------- | ---- |
| 68 | Desktop (Tauri) | Foundation + Quick Assist |
| 69 | Browser extension | YouTube, PDF, web docs |
| 70 | IDE extension | Cursor, VS Code, JetBrains |
| 71 | Mobile | Continuity on the move |
| 72 | Ambient | Always-available voice / notifications |

Lumen web remains the **full platform** for complex workflows (workspace, pipeline, settings depth).

Companions are **ultra-light entry points** into the same intelligence.

---

# Architecture references

- [SHADOW_PRESENCE.md](../architecture/SHADOW_PRESENCE.md) — technical bounded context
- [CONTEXT_HUB.md](../architecture/CONTEXT_HUB.md) — shared context fusion
- [PRESENCE_PERMISSIONS.md](../architecture/PRESENCE_PERMISSIONS.md) — consent model
- [DESKTOP_FOUNDATION.md](../architecture/DESKTOP_FOUNDATION.md) — Tauri MVP
- [PRIVACY_MODEL.md](../architecture/PRIVACY_MODEL.md) — data access rules

Implementation: [TASK-0068](../../planning/Shadow/Sprint-68/TASK-0068.md)
