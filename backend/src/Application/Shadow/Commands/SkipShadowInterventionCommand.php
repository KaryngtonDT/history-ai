<?php

declare(strict_types=1);

namespace App\Application\Shadow\Commands;

final readonly class SkipShadowInterventionCommand
{
    public function __construct(
        public string $videoId,
        public string $sessionId,
        public string $interventionId,
        public float $currentTimeSeconds,
    ) {
    }
}
