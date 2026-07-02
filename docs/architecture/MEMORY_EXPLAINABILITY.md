# Memory Explainability

Shadow Memory surfaces **why** a concept appears in recall.

## UI surfaces

| Surface | What it shows |
|---------|----------------|
| Concepts list | Progress %, exposure count, explanation |
| Timeline | RecordedAt, category, source, linked video/session |
| Connections | From → to label with reason |
| Learning journey | Today, next step, preparation, long-term |

## Prompt transparency

Recall lines in `ShadowWatchPromptBuilder` describe:

- Prerequisite coverage
- Prior progress percentage
- Mastery status
- Related knowledge connections

Users can inspect the same data in `/settings/shadow/memory` without reading raw prompts.

## Reset

`POST /api/shadow/memory/reset` clears knowledge and connections while preserving timeline scope and recording a reset milestone.
