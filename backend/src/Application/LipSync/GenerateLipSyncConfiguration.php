<?php

declare(strict_types=1);

namespace App\Application\LipSync;

final readonly class GenerateLipSyncConfiguration
{
    public function __construct(private bool $enabled)
    {
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
