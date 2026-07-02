# Checkpoints

`CheckpointGenerator` creates one checkpoint per active objective.

Completing a checkpoint (`POST /api/shadow/teaching/checkpoint/{id}/complete`):

- Marks checkpoint completed
- Sets objective status to `mastered` (≥95% progress)
- Records teaching history entry

Watch companion surfaces the next pending checkpoint.
