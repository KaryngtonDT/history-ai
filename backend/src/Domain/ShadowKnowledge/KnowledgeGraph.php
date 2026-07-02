<?php

declare(strict_types=1);

namespace App\Domain\ShadowKnowledge;

use App\Domain\ShadowKnowledge\Exception\InvalidShadowKnowledgeException;

final readonly class KnowledgeGraph
{
    public function __construct(
        private KnowledgeGraphId $id,
        private string $scopeKey,
        private KnowledgeNodeCollection $nodes,
        private KnowledgeEdgeCollection $edges,
        private KnowledgeMasteryCollection $masteries,
        private bool $graphEnabled,
    ) {
        if ('' === trim($scopeKey)) {
            throw new InvalidShadowKnowledgeException('Knowledge graph scope cannot be empty.');
        }
    }

    public static function create(
        ?KnowledgeGraphId $id = null,
        string $scopeKey = 'default',
    ): self {
        return new self(
            $id ?? KnowledgeGraphId::generate(),
            trim($scopeKey),
            KnowledgeNodeCollection::empty(),
            KnowledgeEdgeCollection::empty(),
            KnowledgeMasteryCollection::empty(),
            true,
        );
    }

    public function id(): KnowledgeGraphId
    {
        return $this->id;
    }

    public function scopeKey(): string
    {
        return $this->scopeKey;
    }

    public function nodes(): KnowledgeNodeCollection
    {
        return $this->nodes;
    }

    public function edges(): KnowledgeEdgeCollection
    {
        return $this->edges;
    }

    public function masteries(): KnowledgeMasteryCollection
    {
        return $this->masteries;
    }

    public function graphEnabled(): bool
    {
        return $this->graphEnabled;
    }

    public function withNodes(KnowledgeNodeCollection $nodes): self
    {
        return new self($this->id, $this->scopeKey, $nodes, $this->edges, $this->masteries, $this->graphEnabled);
    }

    public function withEdges(KnowledgeEdgeCollection $edges): self
    {
        return new self($this->id, $this->scopeKey, $this->nodes, $edges, $this->masteries, $this->graphEnabled);
    }

    public function withMasteries(KnowledgeMasteryCollection $masteries): self
    {
        return new self($this->id, $this->scopeKey, $this->nodes, $this->edges, $masteries, $this->graphEnabled);
    }

    public function upsertNode(KnowledgeNode $node): self
    {
        return $this->withNodes($this->nodes->upsert($node));
    }

    public function addEdge(KnowledgeEdge $edge): self
    {
        return $this->withEdges($this->edges->append($edge));
    }

    public function upsertMastery(KnowledgeMastery $mastery): self
    {
        return $this->withMasteries($this->masteries->upsert($mastery));
    }

    public function reset(): self
    {
        return self::create($this->id, $this->scopeKey);
    }
}
