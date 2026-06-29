<?php

declare(strict_types=1);

namespace App\Domain\Graph;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Graph\Exception\InvalidKnowledgeGraphException;
use App\Domain\Relation\ArtifactRelationType;

final readonly class GraphEdge
{
    public function __construct(
        private ArtifactId $sourceArtifactId,
        private ArtifactId $targetArtifactId,
        private ArtifactRelationType $relationType,
        private float $weight = 1.0,
    ) {
        if ($weight < 0) {
            throw new InvalidKnowledgeGraphException('Graph edge weight must be greater than or equal to 0.');
        }
    }

    public function sourceArtifactId(): ArtifactId
    {
        return $this->sourceArtifactId;
    }

    public function targetArtifactId(): ArtifactId
    {
        return $this->targetArtifactId;
    }

    public function relationType(): ArtifactRelationType
    {
        return $this->relationType;
    }

    public function weight(): float
    {
        return $this->weight;
    }
}
