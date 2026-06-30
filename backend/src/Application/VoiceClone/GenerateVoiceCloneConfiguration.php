<?php

declare(strict_types=1);

namespace App\Application\VoiceClone;

final readonly class GenerateVoiceCloneConfiguration
{
    public function __construct(private bool $enabled)
    {
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
