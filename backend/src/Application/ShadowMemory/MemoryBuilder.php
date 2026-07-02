<?php

declare(strict_types=1);

namespace App\Application\ShadowMemory;

use App\Domain\ShadowMemory\KnowledgeItem;
use App\Domain\ShadowMemory\MemoryCategory;
use App\Domain\ShadowMemory\MemoryTimeline;
use App\Domain\ShadowRelationship\RelationshipRepositoryInterface;
use App\Domain\ShadowRelationship\RelationshipTraitType;
use App\Domain\ShadowMemory\ShadowMemoryRepositoryInterface;

final class MemoryBuilder
{
    public function __construct(
        private readonly ShadowMemoryRepositoryInterface $repository,
        private readonly MemoryCollector $collector,
        private readonly MemoryEvolutionEngine $evolutionEngine,
        private readonly ?RelationshipRepositoryInterface $relationshipRepository = null,
    ) {
    }

    public function getOrCreate(string $scopeKey = 'default'): MemoryTimeline
    {
        return $this->repository->findByScope($scopeKey) ?? MemoryTimeline::create(scopeKey: $scopeKey);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function recordPayload(string $scopeKey, array $payload): MemoryTimeline
    {
        $collected = $this->collector->collect($payload);
        $timeline = $this->getOrCreate($scopeKey);
        $timeline = $this->evolutionEngine->evolve($timeline, $collected['kind'], $collected['payload']);
        $this->repository->save($timeline);

        return $timeline;
    }

    public function ingestRelationship(string $scopeKey = 'default'): MemoryTimeline
    {
        $timeline = $this->getOrCreate($scopeKey);

        if ([] !== $timeline->knowledge()->all()) {
            return $timeline;
        }

        $relationship = $this->relationshipRepository?->findByScope($scopeKey);

        if (null === $relationship) {
            return $timeline;
        }

        foreach ($relationship->traits()->byType(RelationshipTraitType::Interest) as $trait) {
            $key = str_replace(' ', '_', strtolower($trait->key()));
            $timeline = $timeline->upsertKnowledge(
                KnowledgeItem::start(
                    $key,
                    $trait->label(),
                    MemoryCategory::Concept,
                    $trait->explanation(),
                ),
            );
        }

        $this->repository->save($timeline);

        return $timeline;
    }

    public function reset(string $scopeKey = 'default'): MemoryTimeline
    {
        $timeline = $this->getOrCreate($scopeKey)->reset();
        $this->repository->save($timeline);

        return $timeline;
    }
}
