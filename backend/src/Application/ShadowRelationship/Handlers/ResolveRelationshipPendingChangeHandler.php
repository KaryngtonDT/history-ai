<?php

declare(strict_types=1);

namespace App\Application\ShadowRelationship\Handlers;

use App\Application\ShadowRelationship\RelationshipEvolutionEngine;
use App\Application\ShadowRelationship\RelationshipJsonMapper;
use App\Application\ShadowRelationship\RelationshipPortraitBuilder;
use App\Application\ShadowRelationship\RelationshipProfileBuilder;
use App\Domain\ShadowRelationship\RelationshipRepositoryInterface;

final class ResolveRelationshipPendingChangeHandler
{
    public function __construct(
        private readonly RelationshipRepositoryInterface $repository,
        private readonly RelationshipProfileBuilder $builder,
        private readonly RelationshipEvolutionEngine $evolutionEngine,
        private readonly RelationshipJsonMapper $mapper,
        private readonly RelationshipPortraitBuilder $portraitBuilder,
    ) {
    }

    /** @return array<string, mixed> */
    public function approve(string $scopeKey, string $changeId): array
    {
        $profile = $this->builder->getOrCreate($scopeKey);
        $change = $profile->pendingChanges()->find($changeId);

        if (null === $change) {
            return ['error' => 'Pending change not found.'];
        }

        $profile = $this->evolutionEngine->applyTrait($profile, $change->proposedTrait());
        $profile = $profile->withPendingChanges($profile->pendingChanges()->replace($change->approve()));
        $this->repository->save($profile);

        return [
            'profile' => $this->mapper->toArray($profile),
            'portrait' => $this->portraitBuilder->build($profile),
        ];
    }

    /** @return array<string, mixed> */
    public function reject(string $scopeKey, string $changeId): array
    {
        $profile = $this->builder->getOrCreate($scopeKey);
        $change = $profile->pendingChanges()->find($changeId);

        if (null === $change) {
            return ['error' => 'Pending change not found.'];
        }

        $profile = $profile->withPendingChanges($profile->pendingChanges()->replace($change->reject()));
        $this->repository->save($profile);

        return [
            'profile' => $this->mapper->toArray($profile),
            'portrait' => $this->portraitBuilder->build($profile),
        ];
    }
}
