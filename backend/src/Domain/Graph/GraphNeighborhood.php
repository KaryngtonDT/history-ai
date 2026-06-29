<?php

declare(strict_types=1);

namespace App\Domain\Graph;

final readonly class GraphNeighborhood
{
    /**
     * @param list<GraphNode> $neighborNodes
     * @param list<GraphEdge> $connectingEdges
     */
    public function __construct(
        private GraphNode $centerNode,
        private array $neighborNodes,
        private array $connectingEdges,
    ) {
    }

    public function centerNode(): GraphNode
    {
        return $this->centerNode;
    }

    /**
     * @return list<GraphNode>
     */
    public function neighborNodes(): array
    {
        return $this->neighborNodes;
    }

    /**
     * @return list<GraphEdge>
     */
    public function connectingEdges(): array
    {
        return $this->connectingEdges;
    }
}
