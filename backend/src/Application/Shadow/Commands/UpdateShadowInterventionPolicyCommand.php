<?php

declare(strict_types=1);

namespace App\Application\Shadow\Commands;

use App\Domain\Shadow\ShadowInterventionPolicy;

final readonly class UpdateShadowInterventionPolicyCommand
{
    public function __construct(
        public string $videoId,
        public string $sessionId,
        public ShadowInterventionPolicy $policy,
    ) {
    }
}
