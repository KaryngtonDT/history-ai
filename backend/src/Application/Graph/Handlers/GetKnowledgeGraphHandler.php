<?php

declare(strict_types=1);

namespace App\Application\Graph\Handlers;

use App\Application\Graph\DTO\KnowledgeGraphResult;
use App\Application\Graph\Queries\GetKnowledgeGraphQuery;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Content\ContentId;
use App\Domain\Graph\KnowledgeGraphBuilder;
use App\Domain\Relation\ArtifactRelationResolver;

final class GetKnowledgeGraphHandler
{
    public function __construct(
        private readonly ArtifactRepositoryInterface $artifactRepository,
    ) {
    }

    public function __invoke(GetKnowledgeGraphQuery $query): KnowledgeGraphResult
    {
        $artifacts = $this->artifactRepository->findByContentId(
            new ContentId($query->contentId),
        );

        $relations = ArtifactRelationResolver::resolve($artifacts);
        $graph = KnowledgeGraphBuilder::build($artifacts, $relations);

        return KnowledgeGraphResult::fromDomain($graph);
    }
}
