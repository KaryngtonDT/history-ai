<?php

declare(strict_types=1);

namespace App\Application\ShadowMemory\Handlers;

use App\Application\ShadowMemory\LearningJourneyBuilder;
use App\Application\ShadowMemory\MemoryBuilder;

final class GetLearningJourneyHandler
{
    public function __construct(
        private readonly MemoryBuilder $builder,
        private readonly LearningJourneyBuilder $journeyBuilder,
    ) {
    }

    /** @return array<string, mixed> */
    public function __invoke(string $scopeKey = 'default'): array
    {
        return [
            'scopeKey' => $scopeKey,
            'journey' => $this->journeyBuilder->build($this->builder->ingestRelationship($scopeKey)),
        ];
    }
}
