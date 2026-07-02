# Shadow Conversational Configuration

**Sprint:** 58  
**Product:** Lumen

---

## Purpose

Users can change Shadow settings using natural language during conversation or from the Teach Shadow panel.

This is **deterministic intent detection** — no LLM is used for configuration decisions.

---

## Application layer

Location: `backend/src/Application/ShadowConfiguration/`

| Component | Role |
| --------- | ---- |
| `ShadowConfigurationIntentDetector` | Regex/phrase rules (EN/FR/DE) |
| `ShadowConfigurationExecutor` | Applies intents to `ShadowIdentity` |
| `ShadowConfigurationConfirmation` | Confirmation messages |
| `ShadowConfigurationInterpreter` | Orchestrates detect → preview → apply |

---

## Supported intents

- Change voice / speed / persona
- Change language and technical term policy
- Change challenge, humor, answer length, conversation style
- Forget preference
- Reset profile (requires confirmation)

---

## Flow

```text
User utterance
     ↓
IntentDetector (deterministic)
     ↓
Preview (from → to)
     ↓
Confirmation (required for reset)
     ↓
Executor → ShadowIdentity + snapshot history
```

---

## API

`POST /api/shadow/identity/configure`

```json
{
  "utterance": "Shadow parle moins vite.",
  "scopeKey": "default",
  "confirmed": false
}
```

Every response includes `explanation`, `preview`, and updated `profile` when applied.
