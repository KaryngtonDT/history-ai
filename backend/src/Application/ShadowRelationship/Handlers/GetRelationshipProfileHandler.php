<?php

declare(strict_types=1);

namespace App\Application\ShadowRelationship\Handlers;

use App\Application\ShadowRelationship\RelationshipJsonMapper;
use App\Application\ShadowRelationship\RelationshipProfileBuilder;
use App\Domain\ShadowRelationship\RelationshipProfile;

final class GetRelationshipProfileHandler
{
    public function __construct(
        private readonly RelationshipProfileBuilder $builder,
        private readonly RelationshipJsonMapper $mapper,
    ) {
    }

    /** @return array<string, mixed> */
    public function __invoke(string $scopeKey = 'default'): array
    {
        $profile = $this->builder->ingestExistingSources($scopeKey);

        return $this->mapper->toArray($profile);
    }
}
