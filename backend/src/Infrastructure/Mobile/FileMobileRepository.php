<?php

declare(strict_types=1);

namespace App\Infrastructure\Mobile;

use App\Domain\Mobile\MobileRepositoryInterface;
use App\Domain\Mobile\MobileWorkspace;
use App\Domain\Mobile\MobileWorkspaceId;
use App\Infrastructure\Storage\JsonFileStore;

final class FileMobileRepository implements MobileRepositoryInterface
{
    public function __construct(
        private readonly JsonFileStore $store,
        private readonly MobilePersistenceMapper $mapper,
    ) {
    }

    public function findByScope(string $scopeKey): ?MobileWorkspace
    {
        foreach ($this->store->listJsonFiles() as $filename) {
            $workspace = $this->read($filename);

            if (null !== $workspace && $workspace->scopeKey() === $scopeKey) {
                return $workspace;
            }
        }

        return null;
    }

    public function findById(MobileWorkspaceId $id): ?MobileWorkspace
    {
        return $this->read($id->value.'.json');
    }

    public function save(MobileWorkspace $workspace): void
    {
        $this->store->write($workspace->id()->value.'.json', $this->mapper->toArray($workspace));
    }

    private function read(string $filename): ?MobileWorkspace
    {
        $data = $this->store->read($filename);

        if (null === $data) {
            return null;
        }

        return $this->mapper->fromJson(json_encode($data, JSON_THROW_ON_ERROR));
    }
}
