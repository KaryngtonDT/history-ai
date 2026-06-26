<?php

declare(strict_types=1);

namespace App\Application\Artifact\Handlers;

use App\Application\Artifact\DTO\ArtifactListItem;
use App\Application\Artifact\DTO\ListArtifactsByContentResult;
use App\Application\Artifact\Queries\ListArtifactsByContentQuery;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Content\ContentId;

final class ListArtifactsByContentHandler
{
    public function __construct(
        private readonly ArtifactRepositoryInterface $artifactRepository,
    ) {
    }

    public function __invoke(ListArtifactsByContentQuery $query): ListArtifactsByContentResult
    {
        $artifacts = $this->artifactRepository->findByContentId(
            new ContentId($query->contentId),
        );

        $items = array_map(
            static fn ($artifact): ArtifactListItem => ArtifactListItem::fromDomain($artifact),
            $artifacts,
        );

        return new ListArtifactsByContentResult($items);
    }
}
