# Shadow Identity

**Sprint:** 58  
**Product:** Lumen  
**Status:** Active

---

## Purpose

Shadow Identity is the bounded context that defines **who Shadow is** for a user: persona, voice profile, language rules, teaching style, and conversation behavior.

It sits **above** Adaptive Learning (Sprint 57) and **below** foundation models:

```text
Foundation Models
        ↓
Shadow Identity
        ↓
Adaptive Learning Profile
        ↓
Explicit User Overrides
        ↓
Conversation Context
        ↓
Final Shadow Behaviour
```

---

## Domain

Location: `backend/src/Domain/ShadowIdentity/`

| Concept | Role |
| ------- | ---- |
| `ShadowIdentity` | Aggregate root with preferences + history |
| `ShadowIdentityPreferences` | Persona, voice, language, teaching, memory |
| `ShadowVoicePersona` | 14 deterministic personas with trait profiles |
| `ShadowIdentitySnapshot` | Append-only configuration history |

---

## Rules

- Immutable aggregate; changes create new instances and history entries
- Explicit user preferences always win over adaptive learning
- No model training or provider weight changes
- Reset restores defaults but preserves scope key
- Every change is explainable via snapshot labels

---

## API

| Method | Path | Purpose |
| ------ | ---- | ------- |
| GET | `/api/shadow/identity/profile` | Read identity profile + DNA + history |
| PUT | `/api/shadow/identity/preferences` | Manual preference updates |
| POST | `/api/shadow/identity/reset` | Reset profile |
| POST | `/api/shadow/identity/configure` | Conversational configuration |
| GET | `/api/shadow/identity/suggestions` | Persona suggestions by content type |

---

## UI

Route: `/settings/shadow` — Shadow Identity Center

---

## Related documents

- [SHADOW_VOICE_STUDIO.md](./SHADOW_VOICE_STUDIO.md)
- [SHADOW_CONFIGURATION.md](./SHADOW_CONFIGURATION.md)
- [SHADOW_LANGUAGE_COMPOSER.md](./SHADOW_LANGUAGE_COMPOSER.md)
- [SHADOW_NARRATIVE_INTELLIGENCE.md](./SHADOW_NARRATIVE_INTELLIGENCE.md)
- [ADAPTIVE_INTELLIGENCE_ENGINE.md](./ADAPTIVE_INTELLIGENCE_ENGINE.md)
