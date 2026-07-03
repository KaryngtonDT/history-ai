# Privacy Model — Shadow Presence

Version: 1.0

Status: Planned (Sprint 68)

Parent: [LUMEN_VISION_2030.md](../vision/LUMEN_VISION_2030.md)

---

# Principles

1. **Local first** — presence preferences and session metadata prefer local/file storage; cloud only when necessary.
2. **Explicit access** — Shadow reads nothing the user did not deliberately provide in that moment.
3. **Revocable** — every grant can be withdrawn; disconnect clears temporary scopes.
4. **Auditable** — presence history shows what was accessed and why.
5. **No surveillance** — no background monitoring of apps, screens, keystrokes, or clipboard.

---

# Data categories

| Category | Stored | User control |
| -------- | ------ | ------------ |
| Presence session metadata | `storage/shadow/presence/` | delete / disconnect |
| Conversation content | existing Shadow session storage | existing reset flows |
| Second Brain / Memory | existing Shadow storage | existing reset flows |
| Surface preferences | presence preferences JSON | settings UI |
| Access history | presence history (explainability) | clear history action |

---

# Proactive behavior

Proactive hints (future Ambient sprint) require:

- explicit opt-in per surface
- explainable trigger recorded in history
- global kill switch in presence settings

Default: **off**.

---

# Companion vs platform

| Surface | Trust boundary |
| ------- | -------------- |
| Lumen web | same-origin, authenticated session |
| Desktop Tauri | local app → HTTPS to backend; no extra OS privileges in S68 |
| Browser ext (future) | page context only with per-site consent |
| IDE ext (future) | workspace scope with project-level consent |

---

# Compliance alignment

Aligns with Vision 2030 principles 4, 5, 9, 10 and engineering privacy-by-design rules.
