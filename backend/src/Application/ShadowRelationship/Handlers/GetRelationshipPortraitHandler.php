<?php

declare(strict_types=1);

namespace App\Application\ShadowRelationship\Handlers;

use App\Application\ShadowRelationship\RelationshipPortraitBuilder;
use App\Application\ShadowRelationship\RelationshipProfileBuilder;

final class GetRelationshipPortraitHandler
{
    public function __construct(
        private readonly RelationshipProfileBuilder $builder,
        private readonly RelationshipPortraitBuilder $portraitBuilder,
    ) {
    }

    /** @return array<string, mixed> */
    public function __invoke(string $scopeKey = 'default'): array
    {
        $profile = $this->builder->ingestExistingSources($scopeKey);

        return $this->portraitBuilder->build($profile);
    }
}
