# Adaptive Intelligence Engine

Version: 1.0

Status: Active (Platform Sprint 57)

---

## Purpose

The Adaptive Intelligence Engine gives Lumen a **controlled, explainable, reversible** learning layer. It observes usage signals and derives deterministic recommendations for Shadow and AI Director.

This is **not** model training, fine-tuning, or provider weight modification.

---

## Controlled learning loop

```text
Usage events (Shadow, reviews, telemetry, quality)
        │
        ▼
LearningSignal (append-only)
        │
        ▼
LearningInsight (derived, with source signal ids)
        │
        ▼
LearningRecommendation (derived, with source insight ids + explanation)
        │
        ▼
Soft application in Shadow / AI Director / Execution Optimizer (when enabled)
```

---

## Bounded context

| Layer | Location |
| ----- | -------- |
| Domain | `backend/src/Domain/Learning/` |
| Application | `backend/src/Application/Learning/` |
| Infrastructure | `backend/src/Infrastructure/Learning/` |
| API | `backend/src/Presentation/Http/Controller/Learning/` |
| UI | `frontend/src/features/learning/`, route `/settings/learning` |

---

## Signal types

Shadow, review, telemetry, and quality mappers produce deterministic `LearningSignal` records (vocabulary questions, challenge outcomes, voice preferences, provider performance, quality scores, etc.).

---

## Deterministic thresholds

`LearningInsightGenerator` applies fixed thresholds (no LLM), for example:

- 3+ vocabulary-related signals → `VocabularyGap`
- 3+ hard challenge skips/failures → lower challenge recommendation
- 3+ easy challenge successes → higher challenge recommendation
- 3+ matching translation or explanation depth signals → style insight
- 3+ voice language signals → `PreferredVoiceLanguage`
- 2+ provider quality observations → `ProviderPreference`

Every insight lists **source signal ids**. Every recommendation lists **source insight ids** and an **explanation**.

---

## Adaptive behavior rules

| Rule | Behavior |
| ---- | -------- |
| Default | Adaptive recommendations **disabled** |
| Manual mode | Shadow tutor off / manual voice / explicit language override unchanged |
| User override | Always wins over learned preferences |
| No profile | Existing behavior unchanged |
| Adaptive off | Insights may still be computed; recommendations not applied |
| Reset | Clears signals, insights, recommendations; keeps preference keys |

---

## Integrations

- **Shadow proactive tutor**: challenge level and explanation style via `LearningAdaptiveShadowPolicyResolver`
- **Shadow answers**: prompt depth hint via `ShadowWatchPromptBuilder`
- **Shadow voice**: soft language preference when not manual and no explicit override
- **AI Director**: soft translation provider preference via `LearningAwarePipelinePlanner`
- **Execution optimizer**: translation style parameter via `LearningAwareExecutionOptimizer`

---

## API

| Method | Path |
| ------ | ---- |
| GET | `/api/learning/profile` |
| GET | `/api/learning/recommendations` |
| POST | `/api/learning/signals` |
| POST | `/api/learning/reset` |
| PUT | `/api/learning/preferences` |

OpenAPI schemas: `LearningProfile`, `LearningSignal`, `LearningInsight`, `LearningRecommendation`, `LearningPreference`.

---

## Privacy and limitations

- Learning stays on-profile scope keys (default: `default`)
- No autonomous model or weight changes
- Threshold-based rules may miss nuanced preferences
- Future path: richer personalization while keeping explainability and user control
