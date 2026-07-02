<?php

declare(strict_types=1);

namespace App\Domain\ShadowGoals;

final readonly class GoalMilestone
{
    public function __construct(
        private string $id,
        private string $goalId,
        private string $label,
        private string $detail,
        private bool $completed,
        private ?\DateTimeImmutable $targetAt,
        private ?\DateTimeImmutable $completedAt,
    ) {
    }

    public static function create(
        string $goalId,
        string $label,
        string $detail = '',
        ?\DateTimeImmutable $targetAt = null,
    ): self {
        return new self(
            bin2hex(random_bytes(8)),
            $goalId,
            $label,
            $detail,
            false,
            $targetAt,
            null,
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

    public function label(): string
    {
        return $this->label;
    }

    public function detail(): string
    {
        return $this->detail;
    }

    public function completed(): bool
    {
        return $this->completed;
    }

    public function targetAt(): ?\DateTimeImmutable
    {
        return $this->targetAt;
    }

    public function completedAt(): ?\DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function complete(): self
    {
        return new self(
            $this->id,
            $this->goalId,
            $this->label,
            $this->detail,
            true,
            $this->targetAt,
            new \DateTimeImmutable(),
        );
    }
}
