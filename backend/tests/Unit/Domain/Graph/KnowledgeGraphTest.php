<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Graph;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Graph\GraphEdge;
use App\Domain\Graph\GraphNode;
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
        self::assertSame([], $graph->nodes());
        self::assertSame([], $graph->edges());
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

        $graph = new KnowledgeGraph([$firstNode, $secondNode], [$firstEdge]);

        self::assertSame(2, $graph->nodeCount());
        self::assertSame(1, $graph->edgeCount());
        self::assertSame(
            [ArtifactType::Transcript, ArtifactType::Summary],
            array_map(
                static fn (GraphNode $node): ArtifactType => $node->artifactType(),
                $graph->nodes(),
            ),
        );
        self::assertSame(
            [ArtifactRelationType::DerivedFrom],
            array_map(
                static fn (GraphEdge $edge): ArtifactRelationType => $edge->relationType(),
                $graph->edges(),
            ),
        );
    }

    public function testReturnedArraysDoNotMutateGraph(): void
    {
        $graph = new KnowledgeGraph([
            new GraphNode(
                new ArtifactId('550e8400-e29b-41d4-a716-446655440001'),
                ArtifactType::Transcript,
                'Transcript',
            ),
        ], []);

        $nodes = $graph->nodes();
        $nodes[] = new GraphNode(
            new ArtifactId('550e8400-e29b-41d4-a716-446655440002'),
            ArtifactType::Summary,
            'Summary',
        );

        self::assertSame(1, $graph->nodeCount());
    }
}
