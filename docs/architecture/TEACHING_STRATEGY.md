# Teaching Strategy

`TeachingStrategyResolver` maps `SessionLearningState` to actionable knobs:

| Strategy kind | Typical trigger |
|---------------|-----------------|
| balanced | default |
| example_driven | low attention |
| challenge_focused | growing confidence + advanced difficulty |
| concise_support | fast pace |
| recovery | high fatigue or struggling confidence |

## Outputs

- `ShadowExplanationStyle` (short, detailed, example_first)
- `ShadowChallengeLevel` (easy, normal, hard)
- Voice style (`calm`, `dynamic`, `neutral`, `storyteller`)
- Speaking pace (`slow`, `normal`, `fast`)
- Prompt hints: examples, analogies, pause offer

## Integration points

- `ShadowWatchPromptBuilder` — answer generation hints
- `shadowVoice.ts` — browser TTS rate from pace/style
- Frontend `ShadowLearningPanel` — live meters + history

No worker or pipeline changes.
