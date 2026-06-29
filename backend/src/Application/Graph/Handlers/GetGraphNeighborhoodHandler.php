<?php

declare(strict_types=1);

namespace App\Application\Graph\Handlers;

use App\Application\Graph\DTO\GraphNeighborhoodResult;
use App\Application\Graph\Queries\GetGraphNeighborhoodQuery;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Content\ContentId;
use App\Domain\Graph\KnowledgeGraphBuilder;
use App\Domain\Relation\ArtifactRelationResolver;

final class GetGraphNeighborhoodHandler
{
    public function __construct(
        private readonly ArtifactRepositoryInterface $artifactRepository,
    ) {
    }

    public function __invoke(GetGraphNeighborhoodQuery $query): ?GraphNeighborhoodResult
    {
        $contentId = new ContentId($query->contentId);
        $artifactId = new ArtifactId($query->artifactId);

        $artifacts = $this->artifactRepository->findByContentId($contentId);
        $relations = ArtifactRelationResolver::resolve($artifacts);
        $graph = KnowledgeGraphBuilder::build($artifacts, $relations);

        $centerNode = $graph->nodes()->findByArtifactId($artifactId);

        if (null === $centerNode) {
            return null;
        }

        return GraphNeighborhoodResult::fromDomain($graph->neighborsOf($centerNode));
    }
}
