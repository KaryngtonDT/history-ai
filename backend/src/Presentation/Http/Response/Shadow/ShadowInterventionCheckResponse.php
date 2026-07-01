<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Shadow;

use App\Application\Shadow\DTO\ShadowInterventionCheckResult;

final readonly class ShadowInterventionCheckResponse
{
    public function __construct(
        public bool $hasIntervention,
        public ?array $intervention,
        public bool $recommendPause,
        public bool $recommendResume,
        public ShadowSessionResponse $session,
    ) {
    }

    public static function fromResult(ShadowInterventionCheckResult $result): self
    {
        return new self(
            hasIntervention: $result->hasIntervention,
            intervention: $result->intervention?->toArray(),
            recommendPause: $result->recommendPause,
            recommendResume: $result->recommendResume,
            session: ShadowSessionResponse::fromResult($result->session),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'hasIntervention' => $this->hasIntervention,
            'intervention' => $this->intervention,
            'recommendPause' => $this->recommendPause,
            'recommendResume' => $this->recommendResume,
            'session' => $this->session->toArray(),
        ];
    }
}
