<?php

declare(strict_types=1);

namespace App\Infrastructure\ShadowKnowledge;

use App\Domain\ShadowKnowledge\KnowledgeGraph;
use App\Domain\ShadowKnowledge\KnowledgeGraphId;
use App\Domain\ShadowKnowledge\ShadowKnowledgeRepositoryInterface;

final class InMemoryShadowKnowledgeRepository implements ShadowKnowledgeRepositoryInterface
{
    /** @var array<string, KnowledgeGraph> */
    private array $graphs = [];

    public function findByScope(string $scopeKey): ?KnowledgeGraph
    {
        foreach ($this->graphs as $graph) {
            if ($graph->scopeKey() === $scopeKey) {
                return $graph;
            }
        }

        return null;
    }

    public function findById(KnowledgeGraphId $id): ?KnowledgeGraph
    {
        return $this->graphs[$id->value] ?? null;
    }

    public function save(KnowledgeGraph $graph): void
    {
        $this->graphs[$graph->id()->value] = $graph;
    }
}
