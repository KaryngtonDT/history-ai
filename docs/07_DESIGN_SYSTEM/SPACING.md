# Spacing

Version: 1.0

Status: Draft

---

# Principle

Generous whitespace — **Apple-level breathing room**.

Scholarly products feel unhurried. Space signals quality.

---

# Scale (4px base)

| Token | Value | Use |
| ----- | ----- | --- |
| `--space-0` | 0 | Reset |
| `--space-1` | 4px | Tight inline gaps |
| `--space-2` | 8px | Icon gaps, chip padding |
| `--space-3` | 12px | Input padding vertical |
| `--space-4` | 16px | Standard gap |
| `--space-5` | 20px | — |
| `--space-6` | 24px | Card padding |
| `--space-8` | 32px | Section gaps |
| `--space-10` | 40px | — |
| `--space-12` | 48px | Page section separation |
| `--space-16` | 64px | Hero / Dashboard top spacing |

---

# Layout

| Context | Value |
| ------- | ----- |
| Page max width | 1200px |
| Content column | 720px (reading) |
| Sidebar width | 240px (if used) |
| Page horizontal padding | `--space-6` mobile, `--space-8` desktop |

---

# Component Spacing

| Component | Internal padding |
| --------- | ---------------- |
| Button (md) | `--space-2` `--space-4` |
| Card | `--space-6` |
| Section | `--space-8` between blocks |
| PageHeader | `--space-8` below title |

---

# Rules

* Prefer `--space-6` and `--space-8` over cramped `--space-2` stacks
* Related items: smaller gap; unrelated sections: larger gap
* Never less than `--space-4` between interactive targets (touch)
