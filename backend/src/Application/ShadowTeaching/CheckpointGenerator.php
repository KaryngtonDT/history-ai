<?php

declare(strict_types=1);

namespace App\Application\ShadowTeaching;

use App\Domain\ShadowTeaching\LearningCheckpoint;
use App\Domain\ShadowTeaching\LearningCheckpointCollection;
use App\Domain\ShadowTeaching\LearningObjective;

final class CheckpointGenerator
{
    public function forObjective(LearningObjective $objective): LearningCheckpoint
    {
        return LearningCheckpoint::create(
            $objective->key(),
            sprintf('Checkpoint: validate %s', $objective->title()),
        );
    }

    public function merge(
        LearningCheckpointCollection $existing,
        LearningObjective $objective,
    ): LearningCheckpointCollection {
        foreach ($existing->all() as $checkpoint) {
            if ($checkpoint->objectiveKey() === $objective->key()) {
                return $existing;
            }
        }

        return $existing->upsert($this->forObjective($objective));
    }
}
