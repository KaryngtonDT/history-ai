<?php

declare(strict_types=1);

namespace App\Application\Shadow\Commands;

final readonly class PauseShadowSessionCommand
{
    public function __construct(
        public string $videoId,
        public string $sessionId,
        public ?float $currentTimeSeconds = null,
    ) {
    }
}
