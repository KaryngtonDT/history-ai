# Revision Engine

Deterministic spaced repetition (no opaque SRS algorithm).

Intervals: **0, 1, 7, 30** days based on exposure and question count.

Each `RevisionItem` includes:

- `dueAt`
- `intervalDays`
- human-readable `reason`

Disable via teaching preferences `revisionEnabled: false`.
