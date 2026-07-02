<?php

declare(strict_types=1);

namespace App\Application\ShadowTeaching;

use App\Domain\ShadowMemory\KnowledgeItem;
use App\Domain\ShadowMemory\KnowledgeProgress;
use App\Domain\ShadowTeaching\LearningObjective;
use App\Domain\ShadowTeaching\TeachingProgressStatus;

final class LearningObjectiveResolver
{
    public function fromKnowledgeItem(KnowledgeItem $item, array $prerequisites = []): LearningObjective
    {
        return LearningObjective::create(
            $item->key(),
            $item->label(),
            $item->explanation(),
            [$item->key()],
            $prerequisites,
            $this->mapStatus($item),
            $item->progressPercent(),
            $item->explanation(),
        );
    }

    private function mapStatus(KnowledgeItem $item): TeachingProgressStatus
    {
        if ($item->questionCount() > 1 && $item->progressPercent() < 80) {
            return TeachingProgressStatus::ReviewNeeded;
        }

        return match ($item->progress()) {
            KnowledgeProgress::New => TeachingProgressStatus::NotStarted,
            KnowledgeProgress::Learning => TeachingProgressStatus::Learning,
            KnowledgeProgress::Practiced => TeachingProgressStatus::Practicing,
            KnowledgeProgress::Mastered => TeachingProgressStatus::Mastered,
        };
    }
}
