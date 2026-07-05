<?php

declare(strict_types=1);

namespace App\Application\ShadowKnowledge;

use App\Application\ShadowMemory\MemoryBuilder;
use App\Application\ShadowTeaching\TeachingBuilder;
use App\Domain\ShadowKnowledge\KnowledgeGraph;
use App\Domain\ShadowKnowledge\ShadowKnowledgeRepositoryInterface;

final class KnowledgeBuilder
{
    public function __construct(
        private readonly ShadowKnowledgeRepositoryInterface $repository,
        private readonly MemoryBuilder $memoryBuilder,
        private readonly TeachingBuilder $teachingBuilder,
        private readonly KnowledgeGraphBuilder $graphBuilder,
    ) {
    }

    public function getOrCreate(string $scopeKey = 'default'): KnowledgeGraph
    {
        return $this->repository->findByScope($scopeKey) ?? KnowledgeGraph::create(scopeKey: $scopeKey);
    }

    public function readGraph(string $scopeKey = 'default'): KnowledgeGraph
    {
        return $this->getOrCreate($scopeKey);
    }

    public function syncGraph(string $scopeKey = 'default'): KnowledgeGraph
    {
        $memory = $this->memoryBuilder->ingestRelationship($scopeKey);
        $teaching = $this->teachingBuilder->syncPlan($scopeKey);
        $graph = $this->graphBuilder->build($this->getOrCreate($scopeKey), $memory, $teaching);
        $this->repository->save($graph);

        return $graph;
    }

    /** @param array<string, mixed> $payload */
    public function recordQuestion(string $scopeKey, array $payload): KnowledgeGraph
    {
        $this->memoryBuilder->recordPayload($scopeKey, [
            'source' => 'shadow',
            'kind' => 'question',
            'data' => $payload,
        ]);
        $this->teachingBuilder->recordQuestion($scopeKey, $payload);

        return $this->syncGraph($scopeKey);
    }

    public function rebuild(string $scopeKey = 'default'): KnowledgeGraph
    {
        return $this->syncGraph($scopeKey);
    }

    public function reset(string $scopeKey = 'default'): KnowledgeGraph
    {
        $graph = $this->getOrCreate($scopeKey)->reset();
        $this->repository->save($graph);

        return $this->syncGraph($scopeKey);
    }
}
