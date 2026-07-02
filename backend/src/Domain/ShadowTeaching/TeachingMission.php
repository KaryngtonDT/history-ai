<?php

declare(strict_types=1);

namespace App\Domain\ShadowTeaching;

final readonly class TeachingMission
{
    public function __construct(
        private int $number,
        private string $title,
        private string $objectiveKey,
        private int $durationMinutes,
        private int $exerciseCount,
        private int $checkpointCount,
        private string $rewardLabel,
        private TeachingProgressStatus $status,
    ) {
    }

    public function number(): int
    {
        return $this->number;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function objectiveKey(): string
    {
        return $this->objectiveKey;
    }

    public function durationMinutes(): int
    {
        return $this->durationMinutes;
    }

    public function exerciseCount(): int
    {
        return $this->exerciseCount;
    }

    public function checkpointCount(): int
    {
        return $this->checkpointCount;
    }

    public function rewardLabel(): string
    {
        return $this->rewardLabel;
    }

    public function status(): TeachingProgressStatus
    {
        return $this->status;
    }
}
