# Animations

Version: 1.0

Status: Draft

---

# Principle

**Calm motion.** Arc Browser fluidity — not bounce, not flash.

Motion communicates state change. It never distracts from reading.

---

# Durations

| Token | Value | Use |
| ----- | ----- | --- |
| `--duration-fast` | 120ms | Hover, focus |
| `--duration-normal` | 200ms | Modals, toasts |
| `--duration-slow` | 300ms | Page transitions |

---

# Easing

| Token | Curve | Use |
| ----- | ----- | --- |
| `--ease-default` | ease-out | Most UI |
| `--ease-in-out` | ease-in-out | Progress, expand |

No spring physics in MVP.

---

# Allowed Animations

| Pattern | Use |
| ------- | --- |
| Opacity fade | Toast enter/exit |
| Subtle translate (4–8px) | Modal, dropdown |
| Progress bar width | Upload, processing |
| Spinner rotation | LoadingState only |

---

# Forbidden

* Infinite pulsing on static content
* Confetti, celebration effects
* Parallax
* Auto-playing motion on Dashboard load

---

# Reduced Motion

Respect `prefers-reduced-motion: reduce` — disable non-essential transitions.

See [ACCESSIBILITY.md](ACCESSIBILITY.md).
