# Exercise Engine

`ExercisePlanner` generates per-objective exercises:

| Type | Purpose |
|------|---------|
| quiz | Core concept recall |
| true_false | Confidence check |
| explain_back | Learner explains in own words |

Answers update exercise status and objective progress via `TeachingProgressUpdater`.

API: `POST /api/shadow/teaching/exercise/{id}/answer`
