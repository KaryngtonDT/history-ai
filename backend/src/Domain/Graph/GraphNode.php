<?php

declare(strict_types=1);

namespace App\Domain\Graph;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactType;

final readonly class GraphNode
{
    public function __construct(
        private ArtifactId $artifactId,
        private ArtifactType $artifactType,
        private string $title,
    ) {
    }

    public function artifactId(): ArtifactId
    {
        return $this->artifactId;
    }

    public function artifactType(): ArtifactType
    {
        return $this->artifactType;
    }

    public function title(): string
    {
        return $this->title;
    }
}
