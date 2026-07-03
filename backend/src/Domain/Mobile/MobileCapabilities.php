<?php

declare(strict_types=1);

namespace App\Domain\Mobile;

final readonly class MobileCapabilities
{
    public function __construct(
        private bool $voice,
        private bool $watchCompanion,
        private bool $notifications,
        private bool $secondBrain,
    ) {
    }

    public static function createDefault(): self
    {
        return new self(
            voice: true,
            watchCompanion: true,
            notifications: true,
            secondBrain: true,
        );
    }

    public function voice(): bool
    {
        return $this->voice;
    }

    public function watchCompanion(): bool
    {
        return $this->watchCompanion;
    }

    public function notifications(): bool
    {
        return $this->notifications;
    }

    public function secondBrain(): bool
    {
        return $this->secondBrain;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            (bool) ($data['voice'] ?? true),
            (bool) ($data['watchCompanion'] ?? true),
            (bool) ($data['notifications'] ?? true),
            (bool) ($data['secondBrain'] ?? true),
        );
    }
}
