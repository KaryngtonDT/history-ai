<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Recommendation;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Graph\GraphEdge;
use App\Domain\Graph\GraphNode;
use App\Domain\Graph\KnowledgeGraph;
use App\Domain\Recommendation\RecommendationEngine;
use App\Domain\Recommendation\RecommendationReason;
use App\Domain\Recommendation\RecommendedArtifact;
use App\Domain\Relation\ArtifactRelationType;
use PHPUnit\Framework\TestCase;

final class RecommendationEngineTest extends TestCase
{
    private RecommendationEngine $engine;

    protected function setUp(): void
    {
        $this->engine = new RecommendationEngine();
    }

    public function testEmptyGraphReturnsEmptyCollection(): void
    {
        $result = $this->engine->recommend(
            KnowledgeGraph::empty(),
            new ArtifactId('550e8400-e29b-41d4-a716-446655440001'),
        );

        self::assertTrue($result->isEmpty());
    }

    public function testIsolatedNodeReturnsEmptyCollection(): void
    {
        $graph = new KnowledgeGraph(
            [
                $this->createNode(
                    '550e8400-e29b-41d4-a716-446655440001',
                    ArtifactType::Summary,
                    'Summary',
                ),
            ],
            [],
        );

        $result = $this->engine->recommend(
            $graph,
            new ArtifactId('550e8400-e29b-41d4-a716-446655440001'),
        );

        self::assertTrue($result->isEmpty());
    }

    public function testNeverRecommendsCurrentArtifact(): void
    {
        $summaryId = new ArtifactId('550e8400-e29b-41d4-a716-446655440002');
        $graph = $this->createSummaryContextGraph();

        $result = $this->engine->recommend($graph, $summaryId);

        self::assertGreaterThan(0, $result->count());
        foreach ($result->recommendations() as $recommendation) {
            self::assertFalse($recommendation->artifactId()->equals($summaryId));
        }
    }

    public function testRecommendsDirectNeighboursOnly(): void
    {
        $summaryId = new ArtifactId('550e8400-e29b-41d4-a716-446655440002');
        $graph = $this->createSummaryContextGraph();

        $result = $this->engine->recommend($graph, $summaryId);

        self::assertSame(
            [
                '550e8400-e29b-41d4-a716-446655440001',
                '550e8400-e29b-41d4-a716-446655440003',
                '550e8400-e29b-41d4-a716-446655440004',
            ],
            array_map(
                static fn (RecommendedArtifact $recommendation): string => $recommendation->artifactId()->value,
                $result->recommendations(),
            ),
        );
    }

    public function testPreservesGraphNodeOrder(): void
    {
        $summaryId = new ArtifactId('550e8400-e29b-41d4-a716-446655440002');
        $graph = new KnowledgeGraph(
            [
                $this->createNode(
                    '550e8400-e29b-41d4-a716-446655440004',
                    ArtifactType::Timeline,
                    'Timeline',
                ),
                $this->createNode(
                    '550e8400-e29b-41d4-a716-446655440002',
                    ArtifactType::Summary,
                    'Summary',
                ),
                $this->createNode(
                    '550e8400-e29b-41d4-a716-446655440001',
                    ArtifactType::Transcript,
                    'Transcript',
                ),
                $this->createNode(
                    '550e8400-e29b-41d4-a716-446655440003',
                    ArtifactType::Quiz,
                    'Quiz',
                ),
            ],
            [
                new GraphEdge(
                    $summaryId,
                    new ArtifactId('550e8400-e29b-41d4-a716-446655440001'),
                    ArtifactRelationType::DerivedFrom,
                ),
                new GraphEdge(
                    new ArtifactId('550e8400-e29b-41d4-a716-446655440003'),
                    $summaryId,
                    ArtifactRelationType::References,
                ),
                new GraphEdge(
                    new ArtifactId('550e8400-e29b-41d4-a716-446655440004'),
                    $summaryId,
                    ArtifactRelationType::References,
                ),
            ],
        );

        $result = $this->engine->recommend($graph, $summaryId);

        self::assertSame(
            ['Timeline', 'Transcript', 'Quiz'],
            array_map(
                static fn (RecommendedArtifact $recommendation): string => $recommendation->title(),
                $result->recommendations(),
            ),
        );
    }

    public function testRemovesDuplicateRecommendations(): void
    {
        $summaryId = new ArtifactId('550e8400-e29b-41d4-a716-446655440002');
        $transcriptId = new ArtifactId('550e8400-e29b-41d4-a716-446655440001');
        $graph = new KnowledgeGraph(
            [
                $this->createNode(
                    '550e8400-e29b-41d4-a716-446655440001',
                    ArtifactType::Transcript,
                    'Transcript',
                ),
                $this->createNode(
                    '550e8400-e29b-41d4-a716-446655440002',
                    ArtifactType::Summary,
                    'Summary',
                ),
            ],
            [
                new GraphEdge($summaryId, $transcriptId, ArtifactRelationType::DerivedFrom),
                new GraphEdge($transcriptId, $summaryId, ArtifactRelationType::Related),
            ],
        );

        $result = $this->engine->recommend($graph, $summaryId);

        self::assertSame(1, $result->count());
        self::assertSame('Transcript', $result->recommendations()[0]->title());
        self::assertSame(
            RecommendationReason::DerivedFrom,
            $result->recommendations()[0]->reason(),
        );
    }

    public function testMapsEdgeReasonToRecommendationReason(): void
    {
        $summaryId = new ArtifactId('550e8400-e29b-41d4-a716-446655440002');
        $graph = $this->createSummaryContextGraph();

        $result = $this->engine->recommend($graph, $summaryId);

        $reasonsByTitle = [];

        foreach ($result->recommendations() as $recommendation) {
            $reasonsByTitle[$recommendation->title()] = $recommendation->reason();
        }

        self::assertSame(RecommendationReason::DerivedFrom, $reasonsByTitle['Transcript']);
        self::assertSame(RecommendationReason::References, $reasonsByTitle['Quiz']);
        self::assertSame(RecommendationReason::References, $reasonsByTitle['Timeline']);
    }

    private function createSummaryContextGraph(): KnowledgeGraph
    {
        $transcriptId = new ArtifactId('550e8400-e29b-41d4-a716-446655440001');
        $summaryId = new ArtifactId('550e8400-e29b-41d4-a716-446655440002');
        $quizId = new ArtifactId('550e8400-e29b-41d4-a716-446655440003');
        $timelineId = new ArtifactId('550e8400-e29b-41d4-a716-446655440004');

        return new KnowledgeGraph(
            [
                $this->createNode(
                    '550e8400-e29b-41d4-a716-446655440001',
                    ArtifactType::Transcript,
                    'Transcript',
                ),
                $this->createNode(
                    '550e8400-e29b-41d4-a716-446655440002',
                    ArtifactType::Summary,
                    'Summary',
                ),
                $this->createNode(
                    '550e8400-e29b-41d4-a716-446655440003',
                    ArtifactType::Quiz,
                    'Quiz',
                ),
                $this->createNode(
                    '550e8400-e29b-41d4-a716-446655440004',
                    ArtifactType::Timeline,
                    'Timeline',
                ),
            ],
            [
                new GraphEdge($summaryId, $transcriptId, ArtifactRelationType::DerivedFrom),
                new GraphEdge($quizId, $summaryId, ArtifactRelationType::References),
                new GraphEdge($timelineId, $summaryId, ArtifactRelationType::References),
            ],
        );
    }

    private function createNode(
        string $artifactId,
        ArtifactType $artifactType,
        string $title,
    ): GraphNode {
        return new GraphNode(
            artifactId: new ArtifactId($artifactId),
            artifactType: $artifactType,
            title: $title,
        );
    }
}
