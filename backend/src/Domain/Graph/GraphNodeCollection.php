<?php

declare(strict_types=1);

namespace App\Domain\Graph;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Graph\Exception\InvalidKnowledgeGraphException;

final readonly class GraphNodeCollection
{
    /** @var list<GraphNode> */
    private array $nodes;

    /** @var array<string, true> */
    private array $nodeIds;

    /**
     * @param list<GraphNode> $nodes
     */
    public function __construct(array $nodes)
    {
        $normalized = [];
        $nodeIds = [];

        foreach ($nodes as $node) {
            $artifactId = $node->artifactId()->value;

            if (isset($nodeIds[$artifactId])) {
                throw new InvalidKnowledgeGraphException(
                    sprintf('Duplicate graph node for artifact "%s".', $artifactId),
                );
            }

            $nodeIds[$artifactId] = true;
            $normalized[] = $node;
        }

        $this->nodes = $normalized;
        $this->nodeIds = $nodeIds;
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return list<GraphNode>
     */
    public function all(): array
    {
        return $this->nodes;
    }

    public function count(): int
    {
        return count($this->nodes);
    }

    public function isEmpty(): bool
    {
        return [] === $this->nodes;
    }

    public function contains(GraphNode $node): bool
    {
        return isset($this->nodeIds[$node->artifactId()->value]);
    }

    public function containsArtifactId(ArtifactId $artifactId): bool
    {
        return isset($this->nodeIds[$artifactId->value]);
    }

    public function findByArtifactId(ArtifactId $artifactId): ?GraphNode
    {
        if (!$this->containsArtifactId($artifactId)) {
            return null;
        }

        foreach ($this->nodes as $node) {
            if ($node->artifactId()->equals($artifactId)) {
                return $node;
            }
        }

        return null;
    }
}
