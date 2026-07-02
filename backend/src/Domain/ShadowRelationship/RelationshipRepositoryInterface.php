<?php

declare(strict_types=1);

namespace App\Domain\ShadowRelationship;

interface RelationshipRepositoryInterface
{
    public function findByScope(string $scopeKey): ?RelationshipProfile;

    public function findById(RelationshipProfileId $id): ?RelationshipProfile;

    public function save(RelationshipProfile $profile): void;
}
