<?php

declare(strict_types=1);

namespace App\Application\Artifact\DTO;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactType;
use DateTimeImmutable;

final readonly class CreateArtifactResult
{
    public function __construct(
        public ArtifactId $artifactId,
        public ArtifactType $type,
        public DateTimeImmutable $createdAt,
    ) {
    }
}
