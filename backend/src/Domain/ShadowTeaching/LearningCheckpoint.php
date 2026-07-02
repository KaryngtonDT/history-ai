<?php

declare(strict_types=1);

namespace App\Domain\ShadowTeaching;

final readonly class LearningCheckpoint
{
    public function __construct(
        private string $id,
        private string $objectiveKey,
        private string $label,
        private bool $completed,
        private ?\DateTimeImmutable $completedAt,
    ) {
    }

    public static function create(string $objectiveKey, string $label): self
    {
        return new self(
            bin2hex(random_bytes(8)),
            $objectiveKey,
            $label,
            false,
            null,
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function objectiveKey(): string
    {
        return $this->objectiveKey;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function completed(): bool
    {
        return $this->completed;
    }

    public function completedAt(): ?\DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function complete(): self
    {
        return new self(
            $this->id,
            $this->objectiveKey,
            $this->label,
            true,
            new \DateTimeImmutable(),
        );
    }
}
