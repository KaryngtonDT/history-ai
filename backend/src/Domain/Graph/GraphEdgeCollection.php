<?php

declare(strict_types=1);

namespace App\Domain\Graph;

use App\Domain\Graph\Exception\InvalidKnowledgeGraphException;

final readonly class GraphEdgeCollection
{
    /** @var list<GraphEdge> */
    private array $edges;

    /**
     * @param list<GraphEdge> $edges
     */
    public function __construct(array $edges)
    {
        $normalized = [];
        $edgeKeys = [];

        foreach ($edges as $edge) {
            $edgeKey = self::edgeKey($edge);

            if (isset($edgeKeys[$edgeKey])) {
                throw new InvalidKnowledgeGraphException(
                    sprintf(
                        'Duplicate graph edge from "%s" to "%s" with relation "%s".',
                        $edge->sourceArtifactId()->value,
                        $edge->targetArtifactId()->value,
                        $edge->relationType()->value,
                    ),
                );
            }

            $edgeKeys[$edgeKey] = true;
            $normalized[] = $edge;
        }

        $this->edges = $normalized;
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return list<GraphEdge>
     */
    public function all(): array
    {
        return $this->edges;
    }

    public function count(): int
    {
        return count($this->edges);
    }

    public function isEmpty(): bool
    {
        return [] === $this->edges;
    }

    public function contains(GraphEdge $edge): bool
    {
        foreach ($this->edges as $candidate) {
            if (self::edgeKey($candidate) === self::edgeKey($edge)) {
                return true;
            }
        }

        return false;
    }

    private static function edgeKey(GraphEdge $edge): string
    {
        return sprintf(
            '%s|%s|%s',
            $edge->sourceArtifactId()->value,
            $edge->targetArtifactId()->value,
            $edge->relationType()->value,
        );
    }
}
