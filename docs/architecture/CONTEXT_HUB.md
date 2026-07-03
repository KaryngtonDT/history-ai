# Context Hub — Architecture

Version: 1.1

Status: Planned (Sprint 68)

Parent: [SHADOW_EVERYWHERE.md](SHADOW_EVERYWHERE.md)

---

# Purpose

The **Context Hub** aggregates existing Shadow bounded contexts into one `PresenceContext` — **no new intelligence**.

---

# Components (S68)

| Component | Role |
| --------- | ---- |
| `PresenceCoordinator` | Entry facade for presence operations |
| `ContextHub` | Orchestrates context fusion |
| `PresenceContextResolver` | Resolves scope from surface + user input |
| `ConversationBridge` | Cross-surface session continuity |
| `PresenceHistoryBuilder` | Builds activity timeline |
| `PresencePermissionEvaluator` | Enforces grants before reads |
| `PresenceDispatcher` | Routes to existing Shadow handlers |
| `PresenceSessionManager` | Connect / disconnect / session lifecycle |

---

# Inputs (read-only)

Memory · Teaching · Mentor · Executive · Knowledge · Identity · Relationship · Second Brain

---

# Rules

1. Hub never calls LLM directly.
2. Fusion is deterministic and unit-tested.
3. Partial context on missing sub-systems — never hard fail.
4. Every build attaches explainability metadata.

See [TASK-0068](../../planning/Shadow/Sprint-68/TASK-0068.md) P68-SLICE-02.
