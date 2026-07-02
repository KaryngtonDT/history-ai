<?php

declare(strict_types=1);

namespace App\Application\ShadowTeaching\Handlers;

use App\Application\ShadowTeaching\TeachingBuilder;
use App\Application\ShadowTeaching\TeachingJsonMapper;

final class GetTeachingPathHandler
{
    public function __construct(
        private readonly TeachingBuilder $builder,
        private readonly TeachingJsonMapper $mapper,
    ) {
    }

    /** @return array<string, mixed> */
    public function __invoke(string $scopeKey = 'default'): array
    {
        $plan = $this->builder->syncPlan($scopeKey);
        $data = $this->mapper->toArray($plan);

        return [
            'scopeKey' => $plan->scopeKey(),
            'path' => $data['path'],
            'currentObjectiveKey' => $data['currentObjectiveKey'],
            'missions' => $data['missions'],
        ];
    }
}
