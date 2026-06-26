<?php

declare(strict_types=1);

namespace App\Application\Artifact\Handlers;

use App\Application\Artifact\Commands\CreateArtifactCommand;
use App\Application\Artifact\DTO\CreateArtifactResult;
use App\Domain\Artifact\Artifact;
use App\Domain\Artifact\ArtifactContent;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Content\ContentId;
use App\Domain\Processing\ProcessingJobId;

final class CreateArtifactHandler
{
    public function __construct(
        private readonly ArtifactRepositoryInterface $artifactRepository,
    ) {
    }

    public function __invoke(CreateArtifactCommand $command): CreateArtifactResult
    {
        $artifact = Artifact::create(
            ArtifactId::generate(),
            new ContentId($command->contentId),
            new ProcessingJobId($command->processingJobId),
            $command->artifactType,
            ArtifactContent::fromString($command->artifactContent),
        );

        $this->artifactRepository->save($artifact);

        return new CreateArtifactResult(
            $artifact->id(),
            $artifact->type(),
            $artifact->createdAt(),
        );
    }
}
