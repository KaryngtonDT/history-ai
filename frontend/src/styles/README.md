# Design Tokens

Status: **Active**

---

# Layers

```text
tokens.css      → primitives (only file with raw hex / rgba values)
variables.css   → semantic aliases (components consume these)
```

Import chain: `index.css` → `variables.css` → `tokens.css`

---

# Rule

Shared components reference `variables.css` semantic names only.

Never hardcode colors, spacing, or sizes in `Component.module.css`.

See Engineering Constitution — Principle 16.

---

# Token categories

| Category | Examples |
| -------- | -------- |
| Colors | `--color-primary`, `--color-surface`, `--color-text` |
| Spacing | `--space-1` … `--space-6` |
| Radius | `--radius-sm`, `--radius-md`, `--radius-lg` |
| Typography | `--font-family`, `--font-sm`, `--font-md`, `--font-lg` |
| Shadow | `--shadow-card`, `--shadow-focus` |
| Sizing | `--size-spinner`, `--layout-sidebar-width` |

Semantic groups (button, badge, card, shell) are defined in `variables.css`.
