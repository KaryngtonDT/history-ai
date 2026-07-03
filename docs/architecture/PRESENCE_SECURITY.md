# Presence Security — Architecture

Version: 1.0

Status: Planned (Sprint 68)

Parent: [SHADOW_EVERYWHERE.md](SHADOW_EVERYWHERE.md)

Replaces: `PRESENCE_PERMISSIONS.md`, `PRIVACY_MODEL.md` (merged into this document for Sprint 68).

---

# Security model

Shadow Presence follows **privacy-first** design from [LUMEN_VISION_2030.md](../vision/LUMEN_VISION_2030.md).

| Rule | Implementation |
| ---- | -------------- |
| No silent capture | No clipboard, screen, or file hooks in S68 |
| Explicit consent | `PresenceConsentManager` records grants |
| Revocable | Disconnect clears temporary scopes |
| Audited | `PresenceAuditLog` + `PresencePermissionHistory` |
| Explainable | `PresenceExplainabilityService` + `GET …/explain` |

---

# Permission defaults

| Capability | Default (S68) |
| ---------- | ------------- |
| `ask_question` | on connect |
| `search_brain` | on connect |
| `resume_conversation` | on connect |
| `read_selection` | **off** — user paste only |
| `read_page_context` | **off** — S69+ with per-site consent |
| `read_workspace` | **off** — S70+ with project consent |
| `proactive_hint` | **off** — opt-in globally |

---

# Audit & explainability

```text
PresenceEvent
  → PresenceAuditLog (what happened)
  → PresencePermissionHistory (which grants used)
  → PresenceExplainabilityService (human-readable why)
  → GET /api/shadow/presence/explain
```

Example response:

```json
{
  "reason": "user_invoked",
  "detail": "Desktop Quick Launcher opened via user action",
  "surface": "desktop",
  "permissionsUsed": ["ask_question", "search_brain"]
}
```

---

# Activity log

`/settings/shadow/presence` — activity log UI backed by `GET /api/shadow/presence/history`.

User can review and clear history (existing Shadow reset patterns apply to presence storage).

---

# Local first

- Presence preferences and session metadata: `storage/shadow/presence/`
- No third-party analytics on presence events
- Cloud sync deferred to Phase IV
