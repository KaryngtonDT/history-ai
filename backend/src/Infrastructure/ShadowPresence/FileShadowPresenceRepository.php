<?php

declare(strict_types=1);

namespace App\Infrastructure\ShadowPresence;

use App\Domain\ShadowPresence\PresenceWorkspace;
use App\Domain\ShadowPresence\PresenceWorkspaceId;
use App\Domain\ShadowPresence\ShadowPresenceRepositoryInterface;
use App\Infrastructure\Storage\JsonFileStore;

final class FileShadowPresenceRepository implements ShadowPresenceRepositoryInterface
{
    public function __construct(
        private readonly JsonFileStore $store,
        private readonly ShadowPresencePersistenceMapper $mapper,
    ) {
    }

    public function findByScope(string $scopeKey): ?PresenceWorkspace
    {
        foreach ($this->store->listJsonFiles() as $filename) {
            $workspace = $this->read($filename);

            if (null !== $workspace && $workspace->scopeKey() === $scopeKey) {
                return $workspace;
            }
        }

        return null;
    }

    public function findById(PresenceWorkspaceId $id): ?PresenceWorkspace
    {
        return $this->read($id->value.'.json');
    }

    public function save(PresenceWorkspace $workspace): void
    {
        $this->store->write($workspace->id()->value.'.json', $this->mapper->toArray($workspace));
    }

    private function read(string $filename): ?PresenceWorkspace
    {
        $data = $this->store->read($filename);

        if (null === $data) {
            return null;
        }

        return $this->mapper->fromJson(json_encode($data, JSON_THROW_ON_ERROR));
    }
}
