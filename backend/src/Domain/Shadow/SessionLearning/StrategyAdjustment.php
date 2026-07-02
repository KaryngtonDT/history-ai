<?php

declare(strict_types=1);

namespace App\Domain\Shadow\SessionLearning;

final readonly class StrategyAdjustment
{
    public function __construct(
        private float $timeSeconds,
        private string $label,
        private string $reason,
    ) {
    }

    public function timeSeconds(): float
    {
        return $this->timeSeconds;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function reason(): string
    {
        return $this->reason;
    }
}
