<?php

declare(strict_types=1);

namespace App\Domain\Runtime;

final readonly class EngineExecutionPlan
{
    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(
        public ResolvedEngine $resolvedEngine,
        public string $planId,
        public string $adapterKey,
        public array $parameters = [],
        public ?RuntimeFallbackPlan $fallbackPlan = null,
        public ?\DateTimeImmutable $resolvedAt = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'planId' => $this->planId,
            'resolvedAt' => ($this->resolvedAt ?? new \DateTimeImmutable())->format(DATE_ATOM),
            'resolvedEngine' => $this->resolvedEngine->toArray(),
            'adapterKey' => $this->adapterKey,
            'parameters' => $this->parameters,
            'fallbackPlan' => $this->fallbackPlan?->toArray(),
        ];
    }
}
