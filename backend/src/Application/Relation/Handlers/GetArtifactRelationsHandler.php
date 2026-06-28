<?php

declare(strict_types=1);

namespace App\Application\Relation\Handlers;

use App\Application\Relation\DTO\ArtifactRelationsResult;
use App\Application\Relation\Queries\GetArtifactRelationsQuery;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Content\ContentId;
use App\Domain\Relation\ArtifactRelationResolver;

final class GetArtifactRelationsHandler
{
    public function __construct(
        private readonly ArtifactRepositoryInterface $artifactRepository,
    ) {
    }

    public function __invoke(GetArtifactRelationsQuery $query): ArtifactRelationsResult
    {
        $artifacts = $this->artifactRepository->findByContentId(
            new ContentId($query->contentId),
        );

        $collection = ArtifactRelationResolver::resolve($artifacts);

        return ArtifactRelationsResult::fromDomain($collection);
    }
}
