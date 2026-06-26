# Icons

Version: 1.0

Status: Draft

---

# Principle

Icons clarify — they do not decorate.

Use sparingly. Pair with text for primary actions.

---

# Library

**Lucide React** (recommended) — consistent stroke, calm, open source.

Alternative: Heroicons (outline only).

One library. One stroke width.

---

# Sizes

| Token | Size | Use |
| ----- | ---- | --- |
| `--icon-sm` | 16px | Inline with text |
| `--icon-md` | 20px | Buttons, list items |
| `--icon-lg` | 24px | Empty states, headers |

---

# Semantic Icons (MVP)

| Concept | Icon direction |
| ------- | -------------- |
| Import PDF | FileText |
| Import YouTube | Youtube |
| Import Audio | Mic |
| Library | Library |
| Processing | Loader / CircleDashed |
| Success | CheckCircle |
| Error | AlertCircle |
| Settings | Settings |

Assets and custom marks: `design/icons/`

---

# Rules

* Stroke icons only (no filled emoji-style in UI chrome)
* `--color-text-secondary` default; accent only when active
* Icon-only buttons require `aria-label`
