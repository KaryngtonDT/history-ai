<?php

declare(strict_types=1);

namespace App\Application\ShadowRelationship\Handlers;

use App\Application\ShadowRelationship\RelationshipJsonMapper;
use App\Application\ShadowRelationship\RelationshipProfileBuilder;

final class ResetRelationshipProfileHandler
{
    public function __construct(
        private readonly RelationshipProfileBuilder $builder,
        private readonly RelationshipJsonMapper $mapper,
    ) {
    }

    /** @return array<string, mixed> */
    public function __invoke(string $scopeKey = 'default'): array
    {
        return $this->mapper->toArray($this->builder->reset($scopeKey));
    }
}
