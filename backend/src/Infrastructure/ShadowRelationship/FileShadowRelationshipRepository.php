<?php

declare(strict_types=1);

namespace App\Infrastructure\ShadowRelationship;

use App\Domain\ShadowRelationship\RelationshipProfile;
use App\Domain\ShadowRelationship\RelationshipProfileId;
use App\Domain\ShadowRelationship\RelationshipRepositoryInterface;
use App\Infrastructure\Storage\JsonFileStore;

final class FileShadowRelationshipRepository implements RelationshipRepositoryInterface
{
    public function __construct(
        private readonly JsonFileStore $store,
        private readonly ShadowRelationshipPersistenceMapper $mapper,
    ) {
    }

    public function findByScope(string $scopeKey): ?RelationshipProfile
    {
        foreach ($this->store->listJsonFiles() as $filename) {
            $profile = $this->readProfile($filename);

            if (null !== $profile && $profile->scopeKey() === $scopeKey) {
                return $profile;
            }
        }

        return null;
    }

    public function findById(RelationshipProfileId $id): ?RelationshipProfile
    {
        return $this->readProfile($this->filenameForId($id->value));
    }

    public function save(RelationshipProfile $profile): void
    {
        $this->store->write(
            $this->filenameForId($profile->id()->value),
            $this->mapper->toArray($profile),
        );
    }

    private function readProfile(string $filename): ?RelationshipProfile
    {
        $data = $this->store->read($filename);

        if (null === $data) {
            return null;
        }

        return $this->mapper->fromJson(json_encode($data, JSON_THROW_ON_ERROR));
    }

    private function filenameForId(string $id): string
    {
        return $id.'.json';
    }
}
