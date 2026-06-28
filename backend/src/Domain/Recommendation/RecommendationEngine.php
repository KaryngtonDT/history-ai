<?php

declare(strict_types=1);

namespace App\Domain\Recommendation;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Graph\GraphEdge;
use App\Domain\Graph\KnowledgeGraph;
use App\Domain\Relation\ArtifactRelationType;

final class RecommendationEngine
{
    public function recommend(
        KnowledgeGraph $graph,
        ArtifactId $currentArtifactId,
    ): RecommendedArtifactCollection {
        if ($graph->isEmpty()) {
            return RecommendedArtifactCollection::empty();
        }

        $neighbourReasons = $this->resolveNeighbourReasons($graph, $currentArtifactId);

        if ([] === $neighbourReasons) {
            return RecommendedArtifactCollection::empty();
        }

        $recommendations = [];

        foreach ($graph->nodes() as $node) {
            $nodeId = $node->artifactId()->value;

            if ($node->artifactId()->equals($currentArtifactId)) {
                continue;
            }

            if (!isset($neighbourReasons[$nodeId])) {
                continue;
            }

            $recommendations[] = new RecommendedArtifact(
                artifactId: $node->artifactId(),
                artifactType: $node->artifactType(),
                title: $node->title(),
                reason: $neighbourReasons[$nodeId],
            );
        }

        return new RecommendedArtifactCollection($recommendations);
    }

    /**
     * @return array<string, RecommendationReason>
     */
    private function resolveNeighbourReasons(
        KnowledgeGraph $graph,
        ArtifactId $currentArtifactId,
    ): array {
        $neighbourReasons = [];

        foreach ($graph->edges() as $edge) {
            $neighbourId = $this->resolveNeighbourId($edge, $currentArtifactId);

            if (null === $neighbourId) {
                continue;
            }

            $neighbourKey = $neighbourId->value;

            if (isset($neighbourReasons[$neighbourKey])) {
                continue;
            }

            $neighbourReasons[$neighbourKey] = $this->mapReason($edge->relationType());
        }

        return $neighbourReasons;
    }

    private function resolveNeighbourId(
        GraphEdge $edge,
        ArtifactId $currentArtifactId,
    ): ?ArtifactId {
        if ($edge->sourceArtifactId()->equals($currentArtifactId)) {
            return $edge->targetArtifactId();
        }

        if ($edge->targetArtifactId()->equals($currentArtifactId)) {
            return $edge->sourceArtifactId();
        }

        return null;
    }

    private function mapReason(ArtifactRelationType $relationType): RecommendationReason
    {
        return match ($relationType) {
            ArtifactRelationType::Related => RecommendationReason::Related,
            ArtifactRelationType::References => RecommendationReason::References,
            ArtifactRelationType::DerivedFrom => RecommendationReason::DerivedFrom,
            ArtifactRelationType::Next => RecommendationReason::Next,
            ArtifactRelationType::Previous => RecommendationReason::Previous,
        };
    }
}
