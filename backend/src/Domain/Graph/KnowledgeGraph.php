<?php

declare(strict_types=1);

namespace App\Domain\Graph;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Graph\Exception\InvalidKnowledgeGraphException;

final readonly class KnowledgeGraph
{
    public function __construct(
        private GraphNodeCollection $nodes,
        private GraphEdgeCollection $edges,
    ) {
    }

    public static function empty(): self
    {
        return new self(GraphNodeCollection::empty(), GraphEdgeCollection::empty());
    }

    public function nodes(): GraphNodeCollection
    {
        return $this->nodes;
    }

    public function edges(): GraphEdgeCollection
    {
        return $this->edges;
    }

    public function nodeCount(): int
    {
        return $this->nodes->count();
    }

    public function edgeCount(): int
    {
        return $this->edges->count();
    }

    public function isEmpty(): bool
    {
        return $this->nodes->isEmpty() && $this->edges->isEmpty();
    }

    public function containsNode(GraphNode $node): bool
    {
        return $this->nodes->contains($node);
    }

    public function containsEdge(GraphEdge $edge): bool
    {
        return $this->edges->contains($edge);
    }

    public function neighborsOf(GraphNode $center): GraphNeighborhood
    {
        if (!$this->containsNode($center)) {
            throw new InvalidKnowledgeGraphException(
                sprintf(
                    'Graph node "%s" is not part of the knowledge graph.',
                    $center->artifactId()->value,
                ),
            );
        }

        /** @var list<GraphNode> $neighborNodes */
        $neighborNodes = [];
        /** @var array<string, true> $seenNeighborIds */
        $seenNeighborIds = [];
        /** @var list<GraphEdge> $connectingEdges */
        $connectingEdges = [];

        foreach ($this->edges->all() as $edge) {
            $neighborId = $this->resolveNeighborId($edge, $center->artifactId());

            if (null === $neighborId) {
                continue;
            }

            $connectingEdges[] = $edge;

            $neighborKey = $neighborId->value;

            if (isset($seenNeighborIds[$neighborKey])) {
                continue;
            }

            $neighborNode = $this->nodes->findByArtifactId($neighborId);

            if (null === $neighborNode) {
                continue;
            }

            $seenNeighborIds[$neighborKey] = true;
            $neighborNodes[] = $neighborNode;
        }

        return new GraphNeighborhood($center, $neighborNodes, $connectingEdges);
    }

    private function resolveNeighborId(GraphEdge $edge, ArtifactId $centerArtifactId): ?ArtifactId
    {
        if ($edge->sourceArtifactId()->equals($centerArtifactId)) {
            return $edge->targetArtifactId();
        }

        if ($edge->targetArtifactId()->equals($centerArtifactId)) {
            return $edge->sourceArtifactId();
        }

        return null;
    }
}
