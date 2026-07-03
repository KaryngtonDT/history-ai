# Context Hub — Architecture

Version: 1.0

Status: Planned (Sprint 68)

Parent: [SHADOW_PRESENCE.md](SHADOW_PRESENCE.md)

---

# Purpose

The **Context Hub** fuses existing Shadow bounded contexts into a single `PresenceContext` payload for any surface.

It adds **no new intelligence** — only aggregation and routing.

---

# Inputs (read-only)

| Source | Data |
| ------ | ---- |
| Second Brain | Concepts, search, diff, timeline |
| Memory | Recent entries, recall |
| Knowledge graph | Related nodes, mastery |
| Mentor | Active goals, missions |
| Executive | Today's agenda, pending decisions |
| Teaching | Active exercises, revision gaps |
| Shadow session | Conversation thread (via ConversationBridge) |

---

# Application components

| Component | Role |
| --------- | ---- |
| `ContextHub` | Orchestrates fusion |
| `PresenceContextBuilder` | Builds deterministic context DTO |
| `ContextResolver` | Resolves scope from surface + user selection |
| `SelectionResolver` | Maps pasted/selected text → concept keys |
| `ConversationBridge` | Links universal conversation across surfaces |
| `PresenceDispatcher` | Routes actions to existing handlers |
| `PresenceHistory` | Records access for explainability |

---

# Output shape (conceptual)

```json
{
  "scopeKey": "default",
  "concept": { "key": "docker", "label": "Docker", "masteryPercent": 42 },
  "relatedConcepts": ["container", "kubernetes"],
  "recentSources": [{ "type": "video", "id": "…", "label": "…" }],
  "activeMission": { "number": 3, "title": "…" },
  "executiveHint": "Revision due for docker_networking",
  "conversationId": "…",
  "explainability": { "reason": "user_invoked", "detail": "Quick Assist shortcut" }
}
```

---

# Rules

1. Hub never calls LLM directly — delegates to existing Shadow handlers.
2. Fusion is deterministic and testable.
3. Missing sub-context degrades gracefully (partial context, not failure).
4. Every build records provenance for explainability.
