<?php

declare(strict_types=1);

namespace App\Domain\ShadowExecutive;

final readonly class ExecutiveWeeklyReview
{
    /** @param list<string> $recommendations */
    public function __construct(
        private string $summary,
        private int $progressPercent,
        private int $knowledgeGrowth,
        private int $completedMissions,
        private int $missedReviews,
        private int $learningMinutes,
        private array $recommendations,
        private string $nextWeekPlan,
    ) {
    }

    public static function empty(): self
    {
        return new self('', 0, 0, 0, 0, 0, [], '');
    }

    /** @param list<string> $recommendations */
    public static function generate(
        string $summary,
        int $progressPercent,
        int $knowledgeGrowth,
        int $completedMissions,
        int $missedReviews,
        int $learningMinutes,
        array $recommendations,
        string $nextWeekPlan,
    ): self {
        return new self(
            $summary,
            $progressPercent,
            $knowledgeGrowth,
            $completedMissions,
            $missedReviews,
            $learningMinutes,
            $recommendations,
            $nextWeekPlan,
        );
    }

    public function summary(): string
    {
        return $this->summary;
    }

    public function progressPercent(): int
    {
        return $this->progressPercent;
    }

    public function knowledgeGrowth(): int
    {
        return $this->knowledgeGrowth;
    }

    public function completedMissions(): int
    {
        return $this->completedMissions;
    }

    public function missedReviews(): int
    {
        return $this->missedReviews;
    }

    public function learningMinutes(): int
    {
        return $this->learningMinutes;
    }

    /** @return list<string> */
    public function recommendations(): array
    {
        return $this->recommendations;
    }

    public function nextWeekPlan(): string
    {
        return $this->nextWeekPlan;
    }
}
