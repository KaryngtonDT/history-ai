# Design Tokens

Version: 1.0

Status: Draft

---

# Role

Design tokens are the **single source of truth** for visual values.

Implementation lives in:

* `design/tokens/` — design reference
* `frontend/src/styles/tokens.css` — code (after validation)

Never hardcode colors or spacing in components.

---

# Token Categories

| Category | Document |
| -------- | -------- |
| Color | [COLORS.md](COLORS.md) |
| Typography | [TYPOGRAPHY.md](TYPOGRAPHY.md) |
| Spacing | [SPACING.md](SPACING.md) |
| Radius | See below |
| Shadow | See below |
| Motion | [ANIMATIONS.md](ANIMATIONS.md) |

---

# Border Radius

| Token | Value | Use |
| ----- | ----- | --- |
| `--radius-sm` | 4px | Chips, badges |
| `--radius-md` | 8px | Inputs, buttons |
| `--radius-lg` | 12px | Cards |
| `--radius-xl` | 16px | Modals, panels |

---

# Shadow

| Token | Use |
| ----- | --- |
| `--shadow-sm` | Subtle elevation — buttons hover |
| `--shadow-md` | Cards |
| `--shadow-lg` | Modals, dropdowns |

Shadows are soft and low-contrast. No heavy drop shadows.

---

# Naming Convention

```text
--color-{role}-{variant}
--space-{scale}
--font-{role}
--text-{size}
--duration-{name}
```

Example: `--color-primary-default`, `--space-md`, `--text-lg`

---

# Dark Mode

Dark mode is **planned** but not MVP.

Tokens must be structured so light/dark pairs can be added without renaming.
