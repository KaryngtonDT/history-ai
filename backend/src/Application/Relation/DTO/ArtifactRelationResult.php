<?php

declare(strict_types=1);

namespace App\Application\Relation\DTO;

use App\Domain\Relation\ArtifactRelation;

final readonly class ArtifactRelationResult
{
    public function __construct(
        public string $sourceArtifactId,
        public string $targetArtifactId,
        public string $type,
    ) {
    }

    public static function fromDomain(ArtifactRelation $relation): self
    {
        return new self(
            sourceArtifactId: $relation->sourceArtifactId()->value,
            targetArtifactId: $relation->targetArtifactId()->value,
            type: $relation->relationType()->value,
        );
    }
}
