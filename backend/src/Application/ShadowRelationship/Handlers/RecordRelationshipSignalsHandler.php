<?php

declare(strict_types=1);

namespace App\Application\ShadowRelationship\Handlers;

use App\Application\ShadowRelationship\RelationshipJsonMapper;
use App\Application\ShadowRelationship\RelationshipProfileBuilder;

final class RecordRelationshipSignalsHandler
{
    public function __construct(
        private readonly RelationshipProfileBuilder $builder,
        private readonly RelationshipJsonMapper $mapper,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function __invoke(string $scopeKey, array $payload): array
    {
        $profile = $this->builder->recordPayload($scopeKey, $payload);

        return $this->mapper->toArray($profile);
    }
}
