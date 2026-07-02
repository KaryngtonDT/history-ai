<?php

declare(strict_types=1);

namespace App\Application\ShadowTeaching\Handlers;

use App\Application\ShadowTeaching\TeachingBuilder;
use App\Application\ShadowTeaching\TeachingJsonMapper;

final class PostTeachingResetHandler
{
    public function __construct(
        private readonly TeachingBuilder $builder,
        private readonly TeachingJsonMapper $mapper,
    ) {
    }

    /** @return array<string, mixed> */
    public function __invoke(string $scopeKey = 'default'): array
    {
        return $this->mapper->toArray($this->builder->reset($scopeKey));
    }
}
