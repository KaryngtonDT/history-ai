<?php

declare(strict_types=1);

namespace App\Application\Graph\DTO;

use App\Domain\Graph\GraphEdge;

final readonly class GraphEdgeResult
{
    public function __construct(
        public string $sourceArtifactId,
        public string $targetArtifactId,
        public string $type,
        public float $weight = 1.0,
    ) {
    }

    public static function fromDomain(GraphEdge $edge): self
    {
        return new self(
            sourceArtifactId: $edge->sourceArtifactId()->value,
            targetArtifactId: $edge->targetArtifactId()->value,
            type: $edge->relationType()->value,
            weight: $edge->weight(),
        );
    }
}
