<?php

declare(strict_types=1);

namespace App\Application\Graph\DTO;

use App\Domain\Graph\GraphEdge;
use App\Domain\Graph\GraphNode;
use App\Domain\Graph\KnowledgeGraph;

final readonly class KnowledgeGraphResult
{
    /**
     * @param list<GraphNodeResult> $nodes
     * @param list<GraphEdgeResult> $edges
     */
    public function __construct(
        public array $nodes,
        public array $edges,
    ) {
    }

    public static function fromDomain(KnowledgeGraph $graph): self
    {
        return new self(
            nodes: array_map(
                static fn (GraphNode $node): GraphNodeResult => GraphNodeResult::fromDomain($node),
                $graph->nodes(),
            ),
            edges: array_map(
                static fn (GraphEdge $edge): GraphEdgeResult => GraphEdgeResult::fromDomain($edge),
                $graph->edges(),
            ),
        );
    }
}
