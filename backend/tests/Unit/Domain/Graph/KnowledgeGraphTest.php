<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Graph;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Graph\Exception\InvalidKnowledgeGraphException;
use App\Domain\Graph\GraphEdge;
use App\Domain\Graph\GraphEdgeCollection;
use App\Domain\Graph\GraphNode;
use App\Domain\Graph\GraphNodeCollection;
use App\Domain\Graph\KnowledgeGraph;
use App\Domain\Relation\ArtifactRelationType;
use PHPUnit\Framework\TestCase;

final class KnowledgeGraphTest extends TestCase
{
    public function testEmptyGraphHasNoNodesOrEdges(): void
    {
        $graph = KnowledgeGraph::empty();

        self::assertTrue($graph->isEmpty());
        self::assertSame(0, $graph->nodeCount());
        self::assertSame(0, $graph->edgeCount());
        self::assertSame([], $graph->nodes()->all());
        self::assertSame([], $graph->edges()->all());
    }

    public function testPreservesNodeAndEdgeOrder(): void
    {
        $firstNode = new GraphNode(
            new ArtifactId('550e8400-e29b-41d4-a716-446655440001'),
            ArtifactType::Transcript,
            'Transcript',
        );
        $secondNode = new GraphNode(
            new ArtifactId('550e8400-e29b-41d4-a716-446655440002'),
            ArtifactType::Summary,
            'Summary',
        );
        $firstEdge = new GraphEdge(
            new ArtifactId('550e8400-e29b-41d4-a716-446655440002'),
            new ArtifactId('550e8400-e29b-41d4-a716-446655440001'),
            ArtifactRelationType::DerivedFrom,
        );

        $graph = new KnowledgeGraph(
            new GraphNodeCollection([$firstNode, $secondNode]),
            new GraphEdgeCollection([$firstEdge]),
        );

        self::assertSame(2, $graph->nodeCount());
        self::assertSame(1, $graph->edgeCount());
        self::assertSame(
            [ArtifactType::Transcript, ArtifactType::Summary],
            array_map(
                static fn (GraphNode $node): ArtifactType => $node->type(),
                $graph->nodes()->all(),
            ),
        );
        self::assertSame(
            [ArtifactRelationType::DerivedFrom],
            array_map(
                static fn (GraphEdge $edge): ArtifactRelationType => $edge->relationType(),
                $graph->edges()->all(),
            ),
        );
    }

    public function testCollectionsDoNotExposeMutableGraphState(): void
    {
        $graph = new KnowledgeGraph(
            new GraphNodeCollection([
                new GraphNode(
                    new ArtifactId('550e8400-e29b-41d4-a716-446655440001'),
                    ArtifactType::Transcript,
                    'Transcript',
                ),
            ]),
            new GraphEdgeCollection([]),
        );

        $nodes = $graph->nodes()->all();
        $nodes[] = new GraphNode(
            new ArtifactId('550e8400-e29b-41d4-a716-446655440002'),
            ArtifactType::Summary,
            'Summary',
        );

        self::assertSame(1, $graph->nodeCount());
    }

    public function testRejectsDuplicateNodes(): void
    {
        $node = new GraphNode(
            new ArtifactId('550e8400-e29b-41d4-a716-446655440001'),
            ArtifactType::Transcript,
            'Transcript',
        );

        $this->expectException(InvalidKnowledgeGraphException::class);

        new GraphNodeCollection([$node, $node]);
    }

    public function testRejectsDuplicateEdges(): void
    {
        $edge = new GraphEdge(
            new ArtifactId('550e8400-e29b-41d4-a716-446655440001'),
            new ArtifactId('550e8400-e29b-41d4-a716-446655440002'),
            ArtifactRelationType::DerivedFrom,
        );

        $this->expectException(InvalidKnowledgeGraphException::class);

        new GraphEdgeCollection([$edge, $edge]);
    }

    public function testContainsNodeAndEdge(): void
    {
        $firstNode = new GraphNode(
            new ArtifactId('550e8400-e29b-41d4-a716-446655440001'),
            ArtifactType::Transcript,
            'Transcript',
        );
        $secondNode = new GraphNode(
            new ArtifactId('550e8400-e29b-41d4-a716-446655440002'),
            ArtifactType::Summary,
            'Summary',
        );
        $edge = new GraphEdge(
            $secondNode->artifactId(),
            $firstNode->artifactId(),
            ArtifactRelationType::DerivedFrom,
        );
        $graph = new KnowledgeGraph(
            new GraphNodeCollection([$firstNode, $secondNode]),
            new GraphEdgeCollection([$edge]),
        );

        self::assertTrue($graph->containsNode($firstNode));
        self::assertTrue($graph->containsEdge($edge));
        self::assertFalse($graph->containsNode(new GraphNode(
            new ArtifactId('550e8400-e29b-41d4-a716-446655440099'),
            ArtifactType::Quiz,
            'Quiz',
        )));
    }

    public function testNeighborsOfReturnsDirectNeighborsOnly(): void
    {
        $transcript = new GraphNode(
            new ArtifactId('550e8400-e29b-41d4-a716-446655440001'),
            ArtifactType::Transcript,
            'Transcript',
        );
        $summary = new GraphNode(
            new ArtifactId('550e8400-e29b-41d4-a716-446655440002'),
            ArtifactType::Summary,
            'Summary',
        );
        $quiz = new GraphNode(
            new ArtifactId('550e8400-e29b-41d4-a716-446655440003'),
            ArtifactType::Quiz,
            'Quiz',
        );
        $derivedEdge = new GraphEdge(
            $summary->artifactId(),
            $transcript->artifactId(),
            ArtifactRelationType::DerivedFrom,
        );
        $referencesEdge = new GraphEdge(
            $quiz->artifactId(),
            $summary->artifactId(),
            ArtifactRelationType::References,
        );
        $graph = new KnowledgeGraph(
            new GraphNodeCollection([$transcript, $summary, $quiz]),
            new GraphEdgeCollection([$derivedEdge, $referencesEdge]),
        );

        $neighborhood = $graph->neighborsOf($summary);

        self::assertTrue($neighborhood->centerNode()->artifactId()->equals($summary->artifactId()));
        self::assertSame(
            ['550e8400-e29b-41d4-a716-446655440001', '550e8400-e29b-41d4-a716-446655440003'],
            array_map(
                static fn (GraphNode $node): string => $node->artifactId()->value,
                $neighborhood->neighborNodes(),
            ),
        );
        self::assertCount(2, $neighborhood->connectingEdges());
    }

    public function testNeighborsOfRejectsUnknownCenterNode(): void
    {
        $graph = KnowledgeGraph::empty();
        $unknownNode = new GraphNode(
            new ArtifactId('550e8400-e29b-41d4-a716-446655440001'),
            ArtifactType::Summary,
            'Summary',
        );

        $this->expectException(InvalidKnowledgeGraphException::class);

        $graph->neighborsOf($unknownNode);
    }
}
