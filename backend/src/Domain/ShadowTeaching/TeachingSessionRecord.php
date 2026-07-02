<?php

declare(strict_types=1);

namespace App\Domain\ShadowTeaching;

final readonly class TeachingSessionRecord
{
    public function __construct(
        private string $id,
        private string $label,
        private string $detail,
        private \DateTimeImmutable $recordedAt,
    ) {
    }

    public static function record(string $label, string $detail): self
    {
        return new self(bin2hex(random_bytes(8)), $label, $detail, new \DateTimeImmutable());
    }

    public function id(): string
    {
        return $this->id;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function detail(): string
    {
        return $this->detail;
    }

    public function recordedAt(): \DateTimeImmutable
    {
        return $this->recordedAt;
    }
}
