<?php

declare(strict_types=1);

namespace App\Domain\ShadowKnowledge;

interface ShadowKnowledgeRepositoryInterface
{
    public function findByScope(string $scopeKey): ?KnowledgeGraph;

    public function findById(KnowledgeGraphId $id): ?KnowledgeGraph;

    public function save(KnowledgeGraph $graph): void;
}
