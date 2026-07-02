<?php

declare(strict_types=1);

namespace App\Application\ShadowTeaching;

use App\Domain\ShadowTeaching\ExerciseStatus;
use App\Domain\ShadowTeaching\TeachingPlan;
use App\Domain\ShadowTeaching\TeachingProgressStatus;
use App\Domain\ShadowTeaching\TeachingSessionRecord;

final class TeachingProgressUpdater
{
    public function answerExercise(TeachingPlan $plan, string $exerciseId, string $answer): TeachingPlan
    {
        $exercise = $plan->exercises()->find($exerciseId);

        if (null === $exercise) {
            return $plan;
        }

        $normalizedAnswer = strtolower(trim($answer));
        $normalizedCorrect = strtolower(trim($exercise->correctAnswer()));
        $isExplainBack = 'explain_back' === $exercise->type()->value;
        $correct = $isExplainBack
            ? strlen($normalizedAnswer) >= 12
            : ($normalizedAnswer === $normalizedCorrect || str_contains($normalizedAnswer, $normalizedCorrect));

        $status = $correct ? ExerciseStatus::Correct : ExerciseStatus::Incorrect;
        $plan = $plan->upsertExercise($exercise->withStatus($status));

        $objective = $plan->objectives()->find($exercise->objectiveKey());

        if (null !== $objective && $correct) {
            $nextStatus = TeachingProgressStatus::Practicing;
            $plan = $plan->upsertObjective($objective->withStatus($nextStatus, max($objective->progressPercent(), 65)));
        }

        return $plan->addHistory(TeachingSessionRecord::record(
            sprintf('Exercise %s', $correct ? 'passed' : 'attempted'),
            $exercise->question(),
        ));
    }

    public function completeCheckpoint(TeachingPlan $plan, string $checkpointId): TeachingPlan
    {
        $checkpoint = $plan->checkpoints()->find($checkpointId);

        if (null === $checkpoint || $checkpoint->completed()) {
            return $plan;
        }

        $plan = $plan->upsertCheckpoint($checkpoint->complete());
        $objective = $plan->objectives()->find($checkpoint->objectiveKey());

        if (null !== $objective) {
            $plan = $plan->upsertObjective($objective->withStatus(
                TeachingProgressStatus::Mastered,
                max($objective->progressPercent(), 95),
            ));
        }

        return $plan->addHistory(TeachingSessionRecord::record(
            'Checkpoint completed',
            $checkpoint->label(),
        ));
    }
}
