<?php

declare(strict_types=1);

namespace App\Domain\ShadowPresence;

final readonly class PresencePermission
{
    public function __construct(
        private PresenceCapability $capability,
        private bool $granted,
    ) {
    }

    public function capability(): PresenceCapability
    {
        return $this->capability;
    }

    public function granted(): bool
    {
        return $this->granted;
    }

    public function withGranted(bool $granted): self
    {
        return new self($this->capability, $granted);
    }
}
