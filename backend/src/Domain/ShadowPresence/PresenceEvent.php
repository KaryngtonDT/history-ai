<?php

declare(strict_types=1);

namespace App\Domain\ShadowPresence;

final readonly class PresenceEvent
{
    /** @param list<string> $permissionsUsed */
    public function __construct(
        private string $id,
        private string $label,
        private PresenceSurface $surface,
        private string $reason,
        private string $detail,
        private \DateTimeImmutable $recordedAt,
        private array $permissionsUsed,
    ) {
    }

    public static function create(
        string $label,
        PresenceSurface $surface,
        string $reason,
        string $detail = '',
        array $permissionsUsed = [],
    ): self {
        return new self(
            bin2hex(random_bytes(8)),
            $label,
            $surface,
            $reason,
            $detail,
            new \DateTimeImmutable(),
            $permissionsUsed,
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function surface(): PresenceSurface
    {
        return $this->surface;
    }

    public function reason(): string
    {
        return $this->reason;
    }

    public function detail(): string
    {
        return $this->detail;
    }

    public function recordedAt(): \DateTimeImmutable
    {
        return $this->recordedAt;
    }

    /** @return list<string> */
    public function permissionsUsed(): array
    {
        return $this->permissionsUsed;
    }
}
