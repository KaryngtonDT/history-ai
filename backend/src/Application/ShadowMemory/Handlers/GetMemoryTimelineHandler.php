<?php

declare(strict_types=1);

namespace App\Application\ShadowMemory\Handlers;

use App\Application\ShadowMemory\MemoryBuilder;
use App\Application\ShadowMemory\MemoryJsonMapper;

final class GetMemoryTimelineHandler
{
    public function __construct(
        private readonly MemoryBuilder $builder,
        private readonly MemoryJsonMapper $mapper,
    ) {
    }

    /** @return array<string, mixed> */
    public function __invoke(string $scopeKey = 'default'): array
    {
        return $this->mapper->toArray($this->builder->ingestRelationship($scopeKey));
    }
}
