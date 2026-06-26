# Accessibility

Version: 1.0

Status: Draft

---

# Commitment

History AI is a **learning product**. Accessibility is not optional.

Target: **WCAG 2.1 Level AA** for MVP screens.

---

# Requirements

| Area | Standard |
| ---- | -------- |
| Color contrast | 4.5:1 body text, 3:1 large text |
| Focus | Visible focus ring on all interactive elements |
| Keyboard | Full keyboard navigation for Dashboard, Upload, Library |
| Screen readers | Semantic HTML, ARIA where needed |
| Motion | Respect `prefers-reduced-motion` |
| Touch targets | Minimum 44×44px |

---

# Component Rules

* Buttons: visible label or `aria-label`
* Dialog/Modal: focus trap, `aria-modal`, escape to close
* Dropzone: keyboard alternative (file input + label)
* Toast: `role="status"` or `role="alert"` by severity
* StatusChip: text label — color never sole indicator

---

# Testing

* Automated: axe in Vitest/Storybook (Phase A)
* Manual: keyboard-only pass before Feature sign-off

---

# Content

* Plain language (see [VOICE_AND_TONE.md](VOICE_AND_TONE.md))
* Error messages: what happened + what to do next
