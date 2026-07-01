<?php

declare(strict_types=1);

namespace App\Application\Shadow\DTO;

use App\Domain\Shadow\ShadowIntervention;
use App\Domain\Shadow\ShadowSession;

final readonly class ShadowInterventionCheckResult
{
    public function __construct(
        public bool $hasIntervention,
        public ?ShadowInterventionResult $intervention,
        public bool $recommendPause,
        public bool $recommendResume,
        public ShadowSessionResult $session,
    ) {
    }

    public static function none(ShadowSession $session): self
    {
        return new self(
            hasIntervention: false,
            intervention: null,
            recommendPause: false,
            recommendResume: false,
            session: ShadowSessionResult::fromDomain($session),
        );
    }

    public static function fromIntervention(
        ShadowIntervention $intervention,
        ShadowSession $session,
    ): self {
        $policy = $session->interventionPolicy();

        return new self(
            hasIntervention: true,
            intervention: ShadowInterventionResult::fromDomain($intervention),
            recommendPause: $policy->allowAutoPause() && $intervention->allowAutoPause(),
            recommendResume: false,
            session: ShadowSessionResult::fromDomain($session),
        );
    }
}
