<?php

declare(strict_types=1);

namespace App\Application\Graph\DTO;

use App\Domain\Graph\GraphEdge;
use App\Domain\Graph\GraphNeighborhood;
use App\Domain\Graph\GraphNode;

final readonly class GraphNeighborhoodResult
{
    /**
     * @param list<GraphNodeResult> $neighbors
     * @param list<GraphEdgeResult> $edges
     */
    public function __construct(
        public GraphNodeResult $center,
        public array $neighbors,
        public array $edges,
    ) {
    }

    public static function fromDomain(GraphNeighborhood $neighborhood): self
    {
        return new self(
            center: GraphNodeResult::fromDomain($neighborhood->centerNode()),
            neighbors: array_map(
                static fn (GraphNode $node): GraphNodeResult => GraphNodeResult::fromDomain($node),
                $neighborhood->neighborNodes(),
            ),
            edges: array_map(
                static fn (GraphEdge $edge): GraphEdgeResult => GraphEdgeResult::fromDomain($edge),
                $neighborhood->connectingEdges(),
            ),
        );
    }
}
