<?php

declare(strict_types=1);

namespace App\Domain\ShadowBrowser;

final readonly class BrowserActivity
{
    /** @param list<string> $permissionsUsed */
    public function __construct(
        private string $id,
        private string $label,
        private BrowserPlatform $platform,
        private string $reason,
        private string $detail,
        private \DateTimeImmutable $recordedAt,
        private array $permissionsUsed,
        private ?string $url,
    ) {
    }

    /** @param list<string> $permissionsUsed */
    public static function create(
        string $label,
        BrowserPlatform $platform,
        string $reason,
        string $detail = '',
        array $permissionsUsed = [],
        ?string $url = null,
    ): self {
        return new self(
            bin2hex(random_bytes(8)),
            $label,
            $platform,
            $reason,
            $detail,
            new \DateTimeImmutable(),
            $permissionsUsed,
            $url,
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

    public function platform(): BrowserPlatform
    {
        return $this->platform;
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

    public function url(): ?string
    {
        return $this->url;
    }
}
