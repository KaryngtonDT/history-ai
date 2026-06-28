<?php

declare(strict_types=1);

namespace App\Domain\Recommendation;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactType;

final readonly class RecommendedArtifact
{
    public function __construct(
        private ArtifactId $artifactId,
        private ArtifactType $artifactType,
        private string $title,
        private RecommendationReason $reason,
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

    public function reason(): RecommendationReason
    {
        return $this->reason;
    }
}
