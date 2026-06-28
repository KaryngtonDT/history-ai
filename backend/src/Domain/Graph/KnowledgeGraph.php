<?php

declare(strict_types=1);

namespace App\Domain\Graph;

final readonly class KnowledgeGraph
{
    /** @var list<GraphNode> */
    private array $nodes;

    /** @var list<GraphEdge> */
    private array $edges;

    /**
     * @param list<GraphNode> $nodes
     * @param list<GraphEdge> $edges
     */
    public function __construct(array $nodes, array $edges)
    {
        $this->nodes = array_values($nodes);
        $this->edges = array_values($edges);
    }

    public static function empty(): self
    {
        return new self([], []);
    }

    /**
     * @return list<GraphNode>
     */
    public function nodes(): array
    {
        return $this->nodes;
    }

    /**
     * @return list<GraphEdge>
     */
    public function edges(): array
    {
        return $this->edges;
    }

    public function nodeCount(): int
    {
        return count($this->nodes);
    }

    public function edgeCount(): int
    {
        return count($this->edges);
    }

    public function isEmpty(): bool
    {
        return [] === $this->nodes && [] === $this->edges;
    }
}
