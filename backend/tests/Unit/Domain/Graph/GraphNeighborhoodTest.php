<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Graph;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Graph\GraphEdge;
use App\Domain\Graph\GraphEdgeCollection;
use App\Domain\Graph\GraphNeighborhood;
use App\Domain\Graph\GraphNode;
use App\Domain\Graph\GraphNodeCollection;
use App\Domain\Graph\KnowledgeGraph;
use App\Domain\Relation\ArtifactRelationType;
use PHPUnit\Framework\TestCase;

final class GraphNeighborhoodTest extends TestCase
{
    public function testExposesCenterNeighborsAndConnectingEdges(): void
    {
        $center = new GraphNode(
            new ArtifactId('550e8400-e29b-41d4-a716-446655440002'),
            ArtifactType::Summary,
            'Summary',
        );
        $transcript = new GraphNode(
            new ArtifactId('550e8400-e29b-41d4-a716-446655440001'),
            ArtifactType::Transcript,
            'Transcript',
        );
        $quiz = new GraphNode(
            new ArtifactId('550e8400-e29b-41d4-a716-446655440003'),
            ArtifactType::Quiz,
            'Quiz',
        );
        $derivedEdge = new GraphEdge(
            $center->artifactId(),
            $transcript->artifactId(),
            ArtifactRelationType::DerivedFrom,
        );
        $referencesEdge = new GraphEdge(
            $quiz->artifactId(),
            $center->artifactId(),
            ArtifactRelationType::References,
        );
        $graph = new KnowledgeGraph(
            new GraphNodeCollection([$center, $transcript, $quiz]),
            new GraphEdgeCollection([$derivedEdge, $referencesEdge]),
        );

        $neighborhood = $graph->neighborsOf($center);

        self::assertInstanceOf(GraphNeighborhood::class, $neighborhood);
        self::assertTrue($neighborhood->centerNode()->artifactId()->equals($center->artifactId()));
        self::assertCount(2, $neighborhood->neighborNodes());
        self::assertCount(2, $neighborhood->connectingEdges());
        self::assertSame(
            [$derivedEdge, $referencesEdge],
            $neighborhood->connectingEdges(),
        );
    }

    public function testPreservesNeighborOrderFromEdgeTraversal(): void
    {
        $center = new GraphNode(
            new ArtifactId('550e8400-e29b-41d4-a716-446655440002'),
            ArtifactType::Summary,
            'Summary',
        );
        $firstNeighbor = new GraphNode(
            new ArtifactId('550e8400-e29b-41d4-a716-446655440004'),
            ArtifactType::Timeline,
            'Timeline',
        );
        $secondNeighbor = new GraphNode(
            new ArtifactId('550e8400-e29b-41d4-a716-446655440003'),
            ArtifactType::Quiz,
            'Quiz',
        );
        $graph = new KnowledgeGraph(
            new GraphNodeCollection([$center, $firstNeighbor, $secondNeighbor]),
            new GraphEdgeCollection([
                new GraphEdge(
                    $firstNeighbor->artifactId(),
                    $center->artifactId(),
                    ArtifactRelationType::References,
                ),
                new GraphEdge(
                    $secondNeighbor->artifactId(),
                    $center->artifactId(),
                    ArtifactRelationType::Related,
                ),
            ]),
        );

        $neighborIds = array_map(
            static fn (GraphNode $node): string => $node->artifactId()->value,
            $graph->neighborsOf($center)->neighborNodes(),
        );

        self::assertSame(
            ['550e8400-e29b-41d4-a716-446655440004', '550e8400-e29b-41d4-a716-446655440003'],
            $neighborIds,
        );
    }
}
