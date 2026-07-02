<?php

declare(strict_types=1);

namespace App\Infrastructure\ShadowKnowledge;

use App\Domain\ShadowKnowledge\KnowledgeGraph;
use App\Domain\ShadowKnowledge\KnowledgeGraphId;
use App\Domain\ShadowKnowledge\ShadowKnowledgeRepositoryInterface;
use App\Infrastructure\Storage\JsonFileStore;

final class FileShadowKnowledgeRepository implements ShadowKnowledgeRepositoryInterface
{
    public function __construct(
        private readonly JsonFileStore $store,
        private readonly ShadowKnowledgePersistenceMapper $mapper,
    ) {
    }

    public function findByScope(string $scopeKey): ?KnowledgeGraph
    {
        foreach ($this->store->listJsonFiles() as $filename) {
            $graph = $this->read($filename);

            if (null !== $graph && $graph->scopeKey() === $scopeKey) {
                return $graph;
            }
        }

        return null;
    }

    public function findById(KnowledgeGraphId $id): ?KnowledgeGraph
    {
        return $this->read($id->value.'.json');
    }

    public function save(KnowledgeGraph $graph): void
    {
        $this->store->write(
            $graph->id()->value.'.json',
            $this->mapper->toArray($graph),
        );
    }

    private function read(string $filename): ?KnowledgeGraph
    {
        $data = $this->store->read($filename);

        if (null === $data) {
            return null;
        }

        return $this->mapper->fromJson(json_encode($data, JSON_THROW_ON_ERROR));
    }
}
