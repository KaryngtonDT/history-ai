<?php

declare(strict_types=1);

namespace App\Domain\ShadowMentor;

final readonly class MentorMission
{
    /** @param list<string> $prerequisiteKeys */
    public function __construct(
        private string $id,
        private string $goalId,
        private string $title,
        private string $objective,
        private int $durationMinutes,
        private array $prerequisiteKeys,
        private int $exerciseCount,
        private string $validationLabel,
        private string $unlockedConceptKey,
        private MentorMissionStatus $status,
        private int $progressPercent,
    ) {
    }

    public static function create(
        string $goalId,
        string $title,
        string $objective,
        int $durationMinutes = 20,
        string $unlockedConceptKey = '',
        array $prerequisiteKeys = [],
    ): self {
        return new self(
            bin2hex(random_bytes(8)),
            $goalId,
            $title,
            $objective,
            $durationMinutes,
            $prerequisiteKeys,
            1,
            'Complete checkpoint',
            $unlockedConceptKey,
            MentorMissionStatus::Upcoming,
            0,
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function goalId(): string
    {
        return $this->goalId;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function objective(): string
    {
        return $this->objective;
    }

    public function durationMinutes(): int
    {
        return $this->durationMinutes;
    }

    /** @return list<string> */
    public function prerequisiteKeys(): array
    {
        return $this->prerequisiteKeys;
    }

    public function exerciseCount(): int
    {
        return $this->exerciseCount;
    }

    public function validationLabel(): string
    {
        return $this->validationLabel;
    }

    public function unlockedConceptKey(): string
    {
        return $this->unlockedConceptKey;
    }

    public function status(): MentorMissionStatus
    {
        return $this->status;
    }

    public function progressPercent(): int
    {
        return $this->progressPercent;
    }

    public function activate(): self
    {
        return new self(
            $this->id,
            $this->goalId,
            $this->title,
            $this->objective,
            $this->durationMinutes,
            $this->prerequisiteKeys,
            $this->exerciseCount,
            $this->validationLabel,
            $this->unlockedConceptKey,
            MentorMissionStatus::Active,
            $this->progressPercent,
        );
    }

    public function complete(): self
    {
        return new self(
            $this->id,
            $this->goalId,
            $this->title,
            $this->objective,
            $this->durationMinutes,
            $this->prerequisiteKeys,
            $this->exerciseCount,
            $this->validationLabel,
            $this->unlockedConceptKey,
            MentorMissionStatus::Completed,
            100,
        );
    }
}
