# Knowledge Diff

Pre-consumption insight: what a **new resource** adds vs existing workspace.

## Output

```text
newConcepts: number
knownConcepts: number
revisionDue: number
redundancyPercent: number  (0–100)
novelConceptKeys: string[]
```

## Example UX

- "This video brings **12 new concepts**."
- "**90%** of this video overlaps what you already know."

## Engine

`KnowledgeDiffEngine` compares resource concept keys (from pipeline/graph) against `KnowledgeWorkspace` entries.

Deterministic set operations — no LLM scoring.

## API

`GET /api/shadow/brain/diff?resourceType=video|pdf&resourceId=…`

## Watch integration

Optional panel before playback — read-only, links to Concept Detail for overlaps.
