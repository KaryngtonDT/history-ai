<?php

declare(strict_types=1);

namespace App\Application\Shadow\DTO;

use App\Domain\Shadow\ShadowChallenge;
use App\Domain\Shadow\ShadowIntervention;
use App\Domain\Shadow\ShadowInterventionPolicy;

final readonly class ShadowInterventionPolicyResult
{
    public function __construct(
        public bool $enabled,
        public int $maxInterventionsPerMinute,
        public float $minSecondsBetweenInterventions,
        public string $challengeLevel,
        public string $explanationStyle,
        public bool $autoResume,
        public bool $allowAutoPause,
    ) {
    }

    public static function fromDomain(ShadowInterventionPolicy $policy): self
    {
        return new self(
            enabled: $policy->enabled(),
            maxInterventionsPerMinute: $policy->maxInterventionsPerMinute(),
            minSecondsBetweenInterventions: $policy->minSecondsBetweenInterventions(),
            challengeLevel: $policy->challengeLevel()->value,
            explanationStyle: $policy->explanationStyle()->value,
            autoResume: $policy->autoResume(),
            allowAutoPause: $policy->allowAutoPause(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'enabled' => $this->enabled,
            'maxInterventionsPerMinute' => $this->maxInterventionsPerMinute,
            'minSecondsBetweenInterventions' => $this->minSecondsBetweenInterventions,
            'challengeLevel' => $this->challengeLevel,
            'explanationStyle' => $this->explanationStyle,
            'autoResume' => $this->autoResume,
            'allowAutoPause' => $this->allowAutoPause,
        ];
    }
}
