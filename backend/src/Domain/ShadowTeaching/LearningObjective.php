<?php

declare(strict_types=1);

namespace App\Domain\ShadowTeaching;

final readonly class LearningObjective
{
    /**
     * @param list<string> $concepts
     * @param list<string> $prerequisites
     */
    public function __construct(
        private string $key,
        private string $title,
        private string $description,
        private array $concepts,
        private array $prerequisites,
        private TeachingProgressStatus $status,
        private int $progressPercent,
        private string $explanation,
    ) {
    }

    public static function create(
        string $key,
        string $title,
        string $description = '',
        array $concepts = [],
        array $prerequisites = [],
        TeachingProgressStatus $status = TeachingProgressStatus::NotStarted,
        int $progressPercent = 0,
        string $explanation = '',
    ): self {
        return new self(
            $key,
            $title,
            $description,
            $concepts,
            $prerequisites,
            $status,
            $progressPercent,
            $explanation,
        );
    }

    public function key(): string
    {
        return $this->key;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function description(): string
    {
        return $this->description;
    }

    /** @return list<string> */
    public function concepts(): array
    {
        return $this->concepts;
    }

    /** @return list<string> */
    public function prerequisites(): array
    {
        return $this->prerequisites;
    }

    public function status(): TeachingProgressStatus
    {
        return $this->status;
    }

    public function progressPercent(): int
    {
        return $this->progressPercent;
    }

    public function explanation(): string
    {
        return $this->explanation;
    }

    public function withStatus(TeachingProgressStatus $status, int $progressPercent): self
    {
        return new self(
            $this->key,
            $this->title,
            $this->description,
            $this->concepts,
            $this->prerequisites,
            $status,
            $progressPercent,
            $this->explanation,
        );
    }
}
