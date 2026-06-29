<?php

declare(strict_types=1);

namespace App\Domain\Graph;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactType;

final readonly class GraphNode
{
    public function __construct(
        private ArtifactId $artifactId,
        private ArtifactType $type,
        private string $label,
    ) {
    }

    public function artifactId(): ArtifactId
    {
        return $this->artifactId;
    }

    public function type(): ArtifactType
    {
        return $this->type;
    }

    public function label(): string
    {
        return $this->label;
    }
}
