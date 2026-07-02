<?php

declare(strict_types=1);

namespace App\Domain\ShadowMentor;

final readonly class WeeklyReview
{
    /** @param list<string> $recommendations */
    public function __construct(
        private string $summary,
        private int $progressDelta,
        private int $milestonesCompleted,
        private string $difficultyNote,
        private array $recommendations,
        private bool $adaptationPending,
        private ?\DateTimeImmutable $generatedAt,
    ) {
    }

    public static function empty(): self
    {
        return new self('', 0, 0, '', [], false, null);
    }

    /** @param list<string> $recommendations */
    public static function generate(
        string $summary,
        int $progressDelta,
        int $milestonesCompleted,
        string $difficultyNote,
        array $recommendations,
    ): self {
        return new self(
            $summary,
            $progressDelta,
            $milestonesCompleted,
            $difficultyNote,
            $recommendations,
            [] !== $recommendations,
            new \DateTimeImmutable(),
        );
    }

    public function summary(): string
    {
        return $this->summary;
    }

    public function progressDelta(): int
    {
        return $this->progressDelta;
    }

    public function milestonesCompleted(): int
    {
        return $this->milestonesCompleted;
    }

    public function difficultyNote(): string
    {
        return $this->difficultyNote;
    }

    /** @return list<string> */
    public function recommendations(): array
    {
        return $this->recommendations;
    }

    public function adaptationPending(): bool
    {
        return $this->adaptationPending;
    }

    public function generatedAt(): ?\DateTimeImmutable
    {
        return $this->generatedAt;
    }

    public function approveAdaptation(): self
    {
        return new self(
            $this->summary,
            $this->progressDelta,
            $this->milestonesCompleted,
            $this->difficultyNote,
            $this->recommendations,
            false,
            $this->generatedAt,
        );
    }
}
