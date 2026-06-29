<?php

declare(strict_types=1);

namespace App\Application\Recommendation\Handlers;

use App\Application\Recommendation\DTO\ArtifactRecommendationsResult;
use App\Application\Recommendation\Queries\GetArtifactRecommendationsQuery;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Content\ContentId;
use App\Domain\Graph\KnowledgeGraph;
use App\Domain\Graph\KnowledgeGraphBuilder;
use App\Domain\Recommendation\RecommendationEngine;
use App\Domain\Recommendation\RecommendationScoringEngine;
use App\Domain\Recommendation\RecommendedArtifactCollection;
use App\Domain\Relation\ArtifactRelationResolver;

final class GetArtifactRecommendationsHandler
{
    public function __construct(
        private readonly ArtifactRepositoryInterface $artifactRepository,
        private readonly RecommendationEngine $recommendationEngine,
        private readonly RecommendationScoringEngine $recommendationScoringEngine,
    ) {
    }

    public function __invoke(GetArtifactRecommendationsQuery $query): ArtifactRecommendationsResult
    {
        $contentId = new ContentId($query->contentId);
        $currentArtifactId = new ArtifactId($query->artifactId);

        $artifacts = $this->artifactRepository->findByContentId($contentId);
        $relations = ArtifactRelationResolver::resolve($artifacts);
        $graph = KnowledgeGraphBuilder::build($artifacts, $relations);

        if (!$this->graphContainsArtifact($graph, $currentArtifactId)) {
            return ArtifactRecommendationsResult::fromDomain(
                RecommendedArtifactCollection::empty(),
            );
        }

        $recommendations = $this->recommendationEngine->recommend($graph, $currentArtifactId);
        $scoredRecommendations = $this->recommendationScoringEngine->score($recommendations);

        return ArtifactRecommendationsResult::fromScoredDomain($scoredRecommendations);
    }

    private function graphContainsArtifact(
        KnowledgeGraph $graph,
        ArtifactId $artifactId,
    ): bool {
        foreach ($graph->nodes()->all() as $node) {
            if ($node->artifactId()->equals($artifactId)) {
                return true;
            }
        }

        return false;
    }
}
