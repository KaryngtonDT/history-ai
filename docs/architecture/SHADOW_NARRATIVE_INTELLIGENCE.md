# Shadow Narrative Intelligence

**Sprint:** 58  
**Product:** Lumen

---

## Purpose

Enrich Shadow answers with narrative style hints based on identity preferences — storytelling, documentary, professor, debate, socratic, friendly.

---

## Components

Location: `backend/src/Application/ShadowNarrative/`

| Component | Role |
| --------- | ---- |
| `ShadowAnswerEnricher` | Main enrichment pipeline |
| `ShadowStorytellingDecorator` | Narrative arc instructions |
| `ShadowNarrationDecorator` | Style-specific structure hints |
| `ShadowSpeechDecorator` | Warmth, energy, humor hints |
| `ShadowPersonaSuggestionEngine` | Soft persona suggestions by content category |

---

## Integration

`ShadowIdentityBehaviorResolver` enriches `ShadowWatchPromptBuilder` prompt lines when a profile exists.

Adaptive Learning (Sprint 57) remains a separate soft layer; explicit identity preferences win.

---

## Persona suggestions

Optional, user-controlled suggestions:

- History content → Storyteller
- Technical content → Technical Expert
- Lecture content → Professor

`GET /api/shadow/identity/suggestions?contentCategory=history`

Suggestions never auto-apply.
