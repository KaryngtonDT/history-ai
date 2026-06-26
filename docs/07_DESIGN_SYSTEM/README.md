# Product Design System

Version: 1.0

Status: Draft

---

# Role

This folder defines the **Product Design System** — the visual and interaction language of History AI.

It precedes code. It precedes Storybook. It precedes Features.

```text
Product Design System (here)
        ↓
design/ (wireframes, mockups, tokens)
        ↓
frontend/ (implementation)
```

---

# Identity

**Quiet Intelligence** — Calm, Focused, Scholarly.

---

# Documents

| Document | Purpose |
| -------- | ------- |
| [DESIGN_PHILOSOPHY.md](DESIGN_PHILOSOPHY.md) | Identity, inspirations, one-question-per-screen |
| [DESIGN_TOKENS.md](DESIGN_TOKENS.md) | Token index |
| [COLORS.md](COLORS.md) | Palette — slate, indigo accent, no AI blue |
| [TYPOGRAPHY.md](TYPOGRAPHY.md) | Inter (or Geist), one family |
| [SPACING.md](SPACING.md) | 4px scale, generous whitespace |
| [ICONS.md](ICONS.md) | Lucide, semantic mapping |
| [ANIMATIONS.md](ANIMATIONS.md) | Calm motion, reduced-motion |
| [COMPONENTS.md](COMPONENTS.md) | MVP component priority list |
| [ACCESSIBILITY.md](ACCESSIBILITY.md) | WCAG AA |
| [VOICE_AND_TONE.md](VOICE_AND_TONE.md) | UI copy, terminology |

---

# MVP Pages

Only five pages in MVP:

```text
Dashboard
Library
Content Details
Upload
Settings
```

Wireframes: `design/wireframes/`

---

# Dashboard (start here)

```text
+----------------------------------------------------+

History AI

Transform knowledge into understanding.

[ Import PDF ]

[ Import YouTube ]

[ Import Audio ]

----------------------------------------------

Recent Content

(empty)

+----------------------------------------------------+
```

Question this screen answers: **What can I learn today?**

---

# Governance

* No Feature UI implementation without validated design in `design/mockups/` or `design/wireframes/`
* Design changes that affect tokens require update to this folder first
* Align with [PRODUCT_MANIFESTO](../00_PROJECT/PRODUCT_MANIFESTO.md)

---

# Current Status

**Draft** — wireframes and token JSON pending before Phase A coding.
