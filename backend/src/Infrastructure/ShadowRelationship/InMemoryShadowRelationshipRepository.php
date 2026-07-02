<?php

declare(strict_types=1);

namespace App\Infrastructure\ShadowRelationship;

use App\Domain\ShadowRelationship\RelationshipProfile;
use App\Domain\ShadowRelationship\RelationshipProfileId;
use App\Domain\ShadowRelationship\RelationshipRepositoryInterface;

final class InMemoryShadowRelationshipRepository implements RelationshipRepositoryInterface
{
    /** @var array<string, RelationshipProfile> */
    private array $profiles = [];

    public function findByScope(string $scopeKey): ?RelationshipProfile
    {
        foreach ($this->profiles as $profile) {
            if ($profile->scopeKey() === $scopeKey) {
                return $profile;
            }
        }

        return null;
    }

    public function findById(RelationshipProfileId $id): ?RelationshipProfile
    {
        return $this->profiles[$id->value] ?? null;
    }

    public function save(RelationshipProfile $profile): void
    {
        $this->profiles[$profile->id()->value] = $profile;
    }

    public function clear(): void
    {
        $this->profiles = [];
    }
}
