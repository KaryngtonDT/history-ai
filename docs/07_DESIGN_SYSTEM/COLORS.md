# Colors

Version: 1.0

Status: Draft

---

# Principle

**No "AI blue."** Content is the focus — not the chrome.

Palette: restrained, scholarly, calm.

---

# Semantic Palette

| Role | Name | Intent |
| ---- | ---- | ------ |
| Primary | Slate | Structure, navigation, primary actions |
| Neutral | Gray scale | Backgrounds, borders, secondary text |
| Accent | Indigo | Focus rings, links, selected states — used sparingly |
| Success | Emerald | Completed, ready |
| Warning | Amber | Attention, pending |
| Danger | Rose | Errors, destructive actions |

---

# Light Mode (MVP)

| Token | Role | Guidance |
| ----- | ---- | -------- |
| `--color-bg-base` | Page background | Near-white, warm neutral |
| `--color-bg-subtle` | Sections | Slight contrast from base |
| `--color-bg-elevated` | Cards, modals | White or soft elevation |
| `--color-text-primary` | Headings, body | Slate 900 |
| `--color-text-secondary` | Metadata | Slate 500 |
| `--color-text-muted` | Placeholders | Slate 400 |
| `--color-border-default` | Dividers | Slate 200 |
| `--color-primary-default` | Primary button | Slate 800 |
| `--color-primary-hover` | Primary hover | Slate 700 |
| `--color-accent-default` | Links, focus | Indigo 600 |
| `--color-success-default` | Success chip | Emerald 600 |
| `--color-warning-default` | Warning chip | Amber 600 |
| `--color-danger-default` | Error, danger | Rose 600 |

Exact hex values: define in `design/tokens/colors.json` before frontend implementation.

---

# Usage Rules

* Accent indigo: **≤ 10%** of any screen
* Primary actions: slate, not gradient
* No neon, no purple-to-blue AI gradients
* Status colors only on StatusChip, Badge, alerts — not full backgrounds

---

# Contrast

All text pairings must meet **WCAG AA** (4.5:1 body, 3:1 large text).

See [ACCESSIBILITY.md](ACCESSIBILITY.md).
