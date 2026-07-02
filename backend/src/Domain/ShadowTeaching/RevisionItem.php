<?php

declare(strict_types=1);

namespace App\Domain\ShadowTeaching;

final readonly class RevisionItem
{
    public function __construct(
        private string $conceptKey,
        private string $label,
        private \DateTimeImmutable $dueAt,
        private int $intervalDays,
        private string $reason,
    ) {
    }

    public function conceptKey(): string
    {
        return $this->conceptKey;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function dueAt(): \DateTimeImmutable
    {
        return $this->dueAt;
    }

    public function intervalDays(): int
    {
        return $this->intervalDays;
    }

    public function reason(): string
    {
        return $this->reason;
    }
}
