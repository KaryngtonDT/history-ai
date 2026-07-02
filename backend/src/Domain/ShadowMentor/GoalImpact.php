<?php

declare(strict_types=1);

namespace App\Domain\ShadowMentor;

final readonly class GoalImpact
{
    public function __construct(
        private string $goalId,
        private string $goalTitle,
        private int $impactPercent,
        private string $reason,
    ) {
    }

    public function goalId(): string
    {
        return $this->goalId;
    }

    public function goalTitle(): string
    {
        return $this->goalTitle;
    }

    public function impactPercent(): int
    {
        return $this->impactPercent;
    }

    public function reason(): string
    {
        return $this->reason;
    }
}
