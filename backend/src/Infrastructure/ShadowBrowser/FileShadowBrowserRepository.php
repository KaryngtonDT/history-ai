<?php

declare(strict_types=1);

namespace App\Infrastructure\ShadowBrowser;

use App\Domain\ShadowBrowser\BrowserWorkspace;
use App\Domain\ShadowBrowser\BrowserWorkspaceId;
use App\Domain\ShadowBrowser\BrowserRepositoryInterface;
use App\Infrastructure\Storage\JsonFileStore;

final class FileShadowBrowserRepository implements BrowserRepositoryInterface
{
    public function __construct(
        private readonly JsonFileStore $store,
        private readonly BrowserPersistenceMapper $mapper,
    ) {
    }

    public function findByScope(string $scopeKey): ?BrowserWorkspace
    {
        foreach ($this->store->listJsonFiles() as $filename) {
            $workspace = $this->read($filename);

            if (null !== $workspace && $workspace->scopeKey() === $scopeKey) {
                return $workspace;
            }
        }

        return null;
    }

    public function findById(BrowserWorkspaceId $id): ?BrowserWorkspace
    {
        return $this->read($id->value.'.json');
    }

    public function save(BrowserWorkspace $workspace): void
    {
        $this->store->write($workspace->id()->value.'.json', $this->mapper->toArray($workspace));
    }

    private function read(string $filename): ?BrowserWorkspace
    {
        $data = $this->store->read($filename);

        if (null === $data) {
            return null;
        }

        return $this->mapper->fromJson(json_encode($data, JSON_THROW_ON_ERROR));
    }
}
