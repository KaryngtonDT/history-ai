<?php

declare(strict_types=1);

namespace App\Application\ShadowTeaching;

use App\Domain\ShadowTeaching\LearningObjective;
use App\Domain\ShadowTeaching\TeachingDifficulty;
use App\Domain\ShadowTeaching\TeachingPreferences;
use App\Domain\ShadowTeaching\TeachingProgressStatus;

final class DifficultyPlanner
{
    public function resolve(TeachingPreferences $preferences, LearningObjective $objective): TeachingDifficulty
    {
        if (TeachingProgressStatus::Mastered === $objective->status()) {
            return TeachingDifficulty::Advanced;
        }

        if (TeachingProgressStatus::NotStarted === $objective->status()) {
            return TeachingDifficulty::Easy;
        }

        return $preferences->difficulty();
    }
}
