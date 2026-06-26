# Typography

Version: 1.0

Status: Draft

---

# Principle

**One type family.** Hierarchy comes from size, weight, and space — not from mixing fonts.

---

# Primary Typeface

**Inter** (recommended for MVP)

Alternative: **Geist** — choose one before implementation; do not use both.

---

# Type Scale

| Token | Size | Weight | Use |
| ----- | ---- | ------ | --- |
| `--text-xs` | 12px | 400 | Captions, metadata |
| `--text-sm` | 14px | 400 | Secondary body, labels |
| `--text-base` | 16px | 400 | Body text |
| `--text-lg` | 18px | 500 | Lead paragraphs |
| `--text-xl` | 20px | 600 | Section titles |
| `--text-2xl` | 24px | 600 | Page titles |
| `--text-3xl` | 30px | 600 | Hero / Dashboard headline |

---

# Weights

| Weight | Use |
| ------ | --- |
| 400 | Body, descriptions |
| 500 | Emphasis, labels |
| 600 | Headings, buttons |

Avoid 700+ except rare marketing moments.

---

# Line Height

| Context | Value |
| ------- | ----- |
| Body | 1.5 |
| Headings | 1.25 |
| UI labels | 1.4 |

---

# Letter Spacing

Default for Inter. No tracked uppercase labels except tiny badges (optional `--tracking-wide`).

---

# Rules

* Maximum two sizes on a single card
* Page titles use `--text-2xl` or `--text-3xl` only
* Monospace only for code blocks (future) — not for UI chrome
