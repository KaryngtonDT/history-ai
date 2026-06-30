<?php

declare(strict_types=1);

namespace App\Application\TTS;

final readonly class GenerateAudioConfiguration
{
    public function __construct(private bool $enabled)
    {
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
