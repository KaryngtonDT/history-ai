# Sprint 56.5 — Shadow Multilingual Voice

**Date:** 2026-06-26  
**Status:** Complete

---

## Product goal

Make Shadow speak and listen reliably in **English, French, and German** — separate from UI i18n. Users choose a speaking language; the backend resolves answer language; the browser handles TTS/STT with graceful text fallback.

---

## Commits

| Slice | Message |
|-------|---------|
| 01 | `feat(shadow): add multilingual voice preferences` |
| 02 | `feat(shadow): resolve answer voice language` |
| 03 | `feat(frontend): add multilingual shadow voice settings` |
| 04 | `test(shadow): cover multilingual voice interaction` |
| 05 | `docs(shadow): document multilingual voice support` |

---

## CTO checklist

| Item | Status |
| ---- | ------ |
| Voice languages: EN / FR / DE | ✅ `ShadowVoiceLanguage` |
| Default = target language | ✅ `SameAsTargetLanguage` |
| Fallback = English | ✅ |
| Explicit override in question | ✅ `explique en français`, etc. |
| Answer metadata on API | ✅ `answerLanguage`, `speechLanguage`, `fallbackUsed`, `reason` |
| Frontend speaking language selector | ✅ `ShadowVoiceSettings` |
| Browser voice selection by locale | ✅ `pickBrowserVoice()` |
| Missing voice warning + text fallback | ✅ |
| SpeechRecognition optional | ✅ No failure when unavailable |
| speechSynthesis optional | ✅ No failure when unavailable |
| Manual Q&A + proactive unchanged | ✅ Extended, not duplicated |
| No video pipeline changes | ✅ |

---

## API

```
PUT /api/videos/{videoId}/shadow/sessions/{sessionId}/voice
POST .../ask  (optional interfaceLanguage in body)
```

Answer responses include voice metadata for frontend TTS.

---

## Browser limitations

| API | Limitation |
| --- | ---------- |
| `SpeechRecognition` | Chrome/Edge best support; not all browsers |
| `speechSynthesis` | Voice list varies by OS/browser; may lack FR/DE voices |
| Server TTS | **Not in this sprint** — future upgrade path documented |

---

## Validation

```bash
docker compose build backend
docker compose run --rm --entrypoint php backend bin/phpunit
docker compose run --rm --entrypoint php backend bin/phpunit tests/Functional/OpenApi
cd frontend && npm run build && npm test && npm run check
```

---

## Documentation

- [SHADOW_WATCH_COMPANION.md](../architecture/SHADOW_WATCH_COMPANION.md)
- [SHADOW_PROACTIVE_TUTOR.md](../architecture/SHADOW_PROACTIVE_TUTOR.md)
- [openapi.md](../architecture/openapi.md)
