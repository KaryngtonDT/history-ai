<?php

declare(strict_types=1);

namespace App\Application\Artifact\Commands;

use App\Domain\Artifact\ArtifactType;

final readonly class CreateArtifactCommand
{
    public function __construct(
        public string $contentId,
        public string $processingJobId,
        public ArtifactType $artifactType,
        public string $artifactContent,
    ) {
    }
}
