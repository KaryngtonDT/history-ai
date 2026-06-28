<?php

declare(strict_types=1);

namespace App\Domain\Graph;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Relation\ArtifactRelationType;

final readonly class GraphEdge
{
    public function __construct(
        private ArtifactId $sourceArtifactId,
        private ArtifactId $targetArtifactId,
        private ArtifactRelationType $relationType,
    ) {
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
}
