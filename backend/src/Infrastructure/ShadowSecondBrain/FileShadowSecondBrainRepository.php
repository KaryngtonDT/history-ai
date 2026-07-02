<?php

declare(strict_types=1);

namespace App\Infrastructure\ShadowSecondBrain;

use App\Domain\ShadowSecondBrain\KnowledgeWorkspace;
use App\Domain\ShadowSecondBrain\KnowledgeWorkspaceId;
use App\Domain\ShadowSecondBrain\ShadowSecondBrainRepositoryInterface;
use App\Infrastructure\Storage\JsonFileStore;

final class FileShadowSecondBrainRepository implements ShadowSecondBrainRepositoryInterface
{
    public function __construct(
        private readonly JsonFileStore $store,
        private readonly ShadowSecondBrainPersistenceMapper $mapper,
    ) {
    }

    public function findByScope(string $scopeKey): ?KnowledgeWorkspace
    {
        foreach ($this->store->listJsonFiles() as $filename) {
            $workspace = $this->read($filename);

            if (null !== $workspace && $workspace->scopeKey() === $scopeKey) {
                return $workspace;
            }
        }

        return null;
    }

    public function findById(KnowledgeWorkspaceId $id): ?KnowledgeWorkspace
    {
        return $this->read($id->value.'.json');
    }

    public function save(KnowledgeWorkspace $workspace): void
    {
        $this->store->write(
            $workspace->id()->value.'.json',
            $this->mapper->toArray($workspace),
        );
    }

    private function read(string $filename): ?KnowledgeWorkspace
    {
        $data = $this->store->read($filename);

        if (null === $data) {
            return null;
        }

        return $this->mapper->fromJson(json_encode($data, JSON_THROW_ON_ERROR));
    }
}
