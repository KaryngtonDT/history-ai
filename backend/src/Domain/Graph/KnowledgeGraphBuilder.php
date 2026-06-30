<?php

declare(strict_types=1);

namespace App\Domain\Graph;

use App\Domain\Artifact\Artifact;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Relation\ArtifactRelation;
use App\Domain\Relation\ArtifactRelationCollection;
use App\Domain\Relation\ArtifactRelationType;

final class KnowledgeGraphBuilder
{
    /**
     * @param list<Artifact> $artifacts
     */
    public static function build(array $artifacts, ArtifactRelationCollection $relations): KnowledgeGraph
    {
        if ([] === $artifacts && $relations->isEmpty()) {
            return KnowledgeGraph::empty();
        }

        /** @var list<GraphNode> $nodes */
        $nodes = [];
        /** @var array<string, true> $nodeIds */
        $nodeIds = [];

        foreach ($artifacts as $artifact) {
            $artifactId = $artifact->id()->value;

            if (isset($nodeIds[$artifactId])) {
                continue;
            }

            $nodes[] = new GraphNode(
                $artifact->id(),
                $artifact->type(),
                self::titleForType($artifact->type()),
            );
            $nodeIds[$artifactId] = true;
        }

        /** @var list<GraphEdge> $edges */
        $edges = [];
        /** @var array<string, true> $edgeKeys */
        $edgeKeys = [];

        foreach ($relations->relations() as $relation) {
            if (!self::isEndpointKnown($nodeIds, $relation)) {
                continue;
            }

            $edgeKey = self::edgeKey(
                $relation->sourceArtifactId(),
                $relation->targetArtifactId(),
                $relation->relationType(),
            );

            if (isset($edgeKeys[$edgeKey])) {
                continue;
            }

            $edges[] = new GraphEdge(
                $relation->sourceArtifactId(),
                $relation->targetArtifactId(),
                $relation->relationType(),
            );
            $edgeKeys[$edgeKey] = true;
        }

        return new KnowledgeGraph(
            new GraphNodeCollection($nodes),
            new GraphEdgeCollection($edges),
        );
    }

    private static function titleForType(ArtifactType $type): string
    {
        return match ($type) {
            ArtifactType::Transcript => 'Transcript',
            ArtifactType::Summary => 'Summary',
            ArtifactType::Quiz => 'Quiz',
            ArtifactType::Flashcards => 'Flashcards',
            ArtifactType::Timeline => 'Timeline',
            ArtifactType::Podcast => 'Podcast',
            ArtifactType::Audio => 'Audio',
        };
    }

    /**
     * @param array<string, true> $nodeIds
     */
    private static function isEndpointKnown(array $nodeIds, ArtifactRelation $relation): bool
    {
        return isset($nodeIds[$relation->sourceArtifactId()->value])
            && isset($nodeIds[$relation->targetArtifactId()->value]);
    }

    private static function edgeKey(
        ArtifactId $sourceArtifactId,
        ArtifactId $targetArtifactId,
        ArtifactRelationType $relationType,
    ): string {
        return sprintf(
            '%s|%s|%s',
            $sourceArtifactId->value,
            $targetArtifactId->value,
            $relationType->value,
        );
    }
}
