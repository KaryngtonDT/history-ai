# Presence Permissions — Architecture

Version: 1.0

Status: Planned (Sprint 68)

Parent: [SHADOW_PRESENCE.md](SHADOW_PRESENCE.md)

---

# Model

Each `PresenceSession` carries explicit `PresencePermission` grants.

| Permission | Default | Notes |
| ---------- | ------- | ----- |
| `ask_question` | granted on connect | Core capability |
| `search_brain` | granted on connect | Second Brain lookup |
| `read_selection` | **denied** until user pastes | No clipboard hook in S68 |
| `read_page_context` | denied | Browser/IDE future — opt-in |
| `proactive_hint` | denied | User enables in settings |
| `voice_input` | denied | Opt-in per surface |

---

# Lifecycle

```text
connect(surface, capabilities[])
  → session created with minimal grants
  → user action or settings expands grants
  → disconnect revokes all temporary grants
```

Temporary grants expire after session end or configurable TTL.

---

# Settings UI

`/settings/shadow/presence` — per-surface permission matrix + access history.

---

# Explainability

`GET /api/shadow/presence/history` returns:

- timestamp
- surface
- action
- permissions used
- reason shown to user

---

# Non-negotiable (from Vision 2030)

- No automatic clipboard monitoring
- No automatic code or file reading
- No background app surveillance
- User can revoke all proactive features globally

See [PRIVACY_MODEL.md](PRIVACY_MODEL.md).
