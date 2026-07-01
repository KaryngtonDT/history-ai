<?php

declare(strict_types=1);

namespace App\Application\Shadow\Queries;

final readonly class CheckShadowInterventionQuery
{
    public function __construct(
        public string $videoId,
        public string $sessionId,
        public float $currentTimeSeconds,
    ) {
    }
}
