# Shadow Voice Studio

**Sprint:** 58  
**Product:** Lumen

---

## Purpose

Voice Studio lets users browse voice collections, tune speaking parameters, apply presets, and preview Shadow speech in the browser.

---

## Backend

Location: `backend/src/Application/ShadowVoice/`

| Component | Role |
| --------- | ---- |
| `ShadowVoiceCatalog` | Deterministic voice library |
| `ShadowVoiceCollection` | Storytellers, professors, technical experts, … |
| `ShadowVoicePresetMapper` | Developer, Storyteller, Professor, … |
| `ShadowVoiceStudio` | Library, preview, preset application |

### Engines

| Engine | Status |
| ------ | ------ |
| Browser TTS | Available |
| F5-TTS | Future |
| XTTS | Future |
| OpenVoice | Future |

---

## API

| Method | Path |
| ------ | ---- |
| GET | `/api/shadow/voice/library` |
| GET | `/api/shadow/voice/collections` |
| POST | `/api/shadow/voice/preview` |
| POST | `/api/shadow/voice/preset` |

---

## Frontend

`frontend/src/features/shadow/VoiceStudio/` — embedded in Shadow Identity Center.

Preview uses browser `speechSynthesis` with speed control.
