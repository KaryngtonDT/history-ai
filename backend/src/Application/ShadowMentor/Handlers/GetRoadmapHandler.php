<?php

declare(strict_types=1);

namespace App\Application\ShadowMentor\Handlers;

use App\Application\ShadowMentor\MentorBuilder;
use App\Application\ShadowMentor\MentorJsonMapper;

final class GetRoadmapHandler
{
    public function __construct(
        private readonly MentorBuilder $builder,
        private readonly MentorJsonMapper $mapper,
    ) {
    }

    /** @return array<string, mixed> */
    public function __invoke(string $scopeKey = 'default'): array
    {
        return $this->mapper->roadmapResponse($this->builder->syncPlan($scopeKey));
    }
}
