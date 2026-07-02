<?php

declare(strict_types=1);

namespace App\Application\ShadowTeaching\Handlers;

use App\Application\ShadowTeaching\TeachingAdvisor;
use App\Application\ShadowTeaching\TeachingBuilder;
use App\Application\ShadowTeaching\TeachingJsonMapper;

final class GetTeachingCurrentHandler
{
    public function __construct(
        private readonly TeachingBuilder $builder,
        private readonly TeachingJsonMapper $mapper,
        private readonly TeachingAdvisor $advisor,
    ) {
    }

    /** @return array<string, mixed> */
    public function __invoke(string $scopeKey = 'default'): array
    {
        return $this->mapper->current($this->builder->syncPlan($scopeKey), $this->advisor);
    }
}
