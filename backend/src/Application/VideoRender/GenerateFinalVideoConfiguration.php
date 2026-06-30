<?php

declare(strict_types=1);

namespace App\Application\VideoRender;

final readonly class GenerateFinalVideoConfiguration
{
    public function __construct(private bool $enabled)
    {
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
