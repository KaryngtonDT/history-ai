<?php

declare(strict_types=1);

namespace App\Infrastructure\ShadowIdentity;

use App\Domain\ShadowIdentity\ShadowIdentity;
use App\Domain\ShadowIdentity\ShadowIdentityId;
use App\Domain\ShadowIdentity\ShadowIdentityRepositoryInterface;
use App\Infrastructure\Storage\JsonFileStore;

final class FileShadowIdentityRepository implements ShadowIdentityRepositoryInterface
{
    public function __construct(
        private readonly JsonFileStore $store,
        private readonly ShadowIdentityPersistenceMapper $mapper,
    ) {
    }

    public function findByScope(string $scopeKey): ?ShadowIdentity
    {
        foreach ($this->store->listJsonFiles() as $filename) {
            $identity = $this->readIdentity($filename);

            if (null !== $identity && $identity->scopeKey() === $scopeKey) {
                return $identity;
            }
        }

        return null;
    }

    public function findById(ShadowIdentityId $id): ?ShadowIdentity
    {
        return $this->readIdentity($this->filenameForId($id->value));
    }

    public function save(ShadowIdentity $identity): void
    {
        $this->store->write(
            $this->filenameForId($identity->id()->value),
            $this->mapper->toArray($identity),
        );
    }

    public function clear(): void
    {
        foreach ($this->store->listJsonFiles() as $filename) {
            $this->store->delete($filename);
        }
    }

    private function readIdentity(string $filename): ?ShadowIdentity
    {
        $data = $this->store->read($filename);

        if (null === $data) {
            return null;
        }

        return $this->mapper->fromJson(json_encode($data, JSON_THROW_ON_ERROR));
    }

    private function filenameForId(string $id): string
    {
        return $id . '.json';
    }
}
