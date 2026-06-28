<?php

declare(strict_types=1);

namespace App\Domain\Relation;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Relation\Exception\InvalidArtifactRelationException;

final readonly class ArtifactRelation
{
    public function __construct(
        private ArtifactId $sourceArtifactId,
        private ArtifactId $targetArtifactId,
        private ArtifactRelationType $relationType,
    ) {
        if ($sourceArtifactId->equals($targetArtifactId)) {
            throw new InvalidArtifactRelationException(
                'An artifact relation cannot reference the same artifact as both source and target.',
            );
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
}
