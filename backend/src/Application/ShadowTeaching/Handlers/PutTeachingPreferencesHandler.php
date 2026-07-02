<?php

declare(strict_types=1);

namespace App\Application\ShadowTeaching\Handlers;

use App\Application\ShadowTeaching\TeachingBuilder;
use App\Application\ShadowTeaching\TeachingJsonMapper;

final class PutTeachingPreferencesHandler
{
    public function __construct(
        private readonly TeachingBuilder $builder,
        private readonly TeachingJsonMapper $mapper,
    ) {
    }

    /** @param array<string, mixed> $payload */
    public function __invoke(string $scopeKey, array $payload): array
    {
        return $this->mapper->toArray($this->builder->updatePreferences($scopeKey, $payload));
    }
}
