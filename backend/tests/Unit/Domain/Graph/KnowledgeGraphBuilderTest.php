<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Graph;

use App\Domain\Artifact\Artifact;
use App\Domain\Artifact\ArtifactContent;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Content\ContentId;
use App\Domain\Graph\GraphEdge;
use App\Domain\Graph\GraphNode;
use App\Domain\Graph\KnowledgeGraphBuilder;
use App\Domain\Processing\ProcessingJobId;
use App\Domain\Relation\ArtifactRelation;
use App\Domain\Relation\ArtifactRelationCollection;
use App\Domain\Relation\ArtifactRelationResolver;
use App\Domain\Relation\ArtifactRelationType;
use PHPUnit\Framework\TestCase;

final class KnowledgeGraphBuilderTest extends TestCase
{
    public function testEmptyInputsReturnEmptyGraph(): void
    {
        $graph = KnowledgeGraphBuilder::build([], ArtifactRelationCollection::empty());

        self::assertTrue($graph->isEmpty());
    }

    public function testBuildsNodesFromArtifactsWithoutRelations(): void
    {
        $transcript = $this->createArtifact(
            '550e8400-e29b-41d4-a716-446655440001',
            ArtifactType::Transcript,
        );
        $summary = $this->createArtifact(
            '550e8400-e29b-41d4-a716-446655440002',
            ArtifactType::Summary,
        );

        $graph = KnowledgeGraphBuilder::build(
            [$transcript, $summary],
            ArtifactRelationCollection::empty(),
        );

        self::assertSame(2, $graph->nodeCount());
        self::assertSame(0, $graph->edgeCount());
        self::assertSame(
            ['Transcript', 'Summary'],
            array_map(static fn (GraphNode $node): string => $node->title(), $graph->nodes()),
        );
    }

    public function testBuildsEdgesFromRelations(): void
    {
        $transcript = $this->createArtifact(
            '550e8400-e29b-41d4-a716-446655440001',
            ArtifactType::Transcript,
        );
        $summary = $this->createArtifact(
            '550e8400-e29b-41d4-a716-446655440002',
            ArtifactType::Summary,
        );
        $relations = new ArtifactRelationCollection([
            new ArtifactRelation(
                $summary->id(),
                $transcript->id(),
                ArtifactRelationType::DerivedFrom,
            ),
        ]);

        $graph = KnowledgeGraphBuilder::build([$transcript, $summary], $relations);

        self::assertSame(2, $graph->nodeCount());
        self::assertSame(1, $graph->edgeCount());
        self::assertSame(
            [ArtifactRelationType::DerivedFrom],
            array_map(
                static fn (GraphEdge $edge): ArtifactRelationType => $edge->relationType(),
                $graph->edges(),
            ),
        );
    }

    public function testPreservesArtifactOrderForNodes(): void
    {
        $quiz = $this->createArtifact(
            '550e8400-e29b-41d4-a716-446655440003',
            ArtifactType::Quiz,
        );
        $transcript = $this->createArtifact(
            '550e8400-e29b-41d4-a716-446655440001',
            ArtifactType::Transcript,
        );
        $summary = $this->createArtifact(
            '550e8400-e29b-41d4-a716-446655440002',
            ArtifactType::Summary,
        );

        $graph = KnowledgeGraphBuilder::build(
            [$quiz, $transcript, $summary],
            ArtifactRelationCollection::empty(),
        );

        self::assertSame(
            [
                ArtifactType::Quiz,
                ArtifactType::Transcript,
                ArtifactType::Summary,
            ],
            array_map(
                static fn (GraphNode $node): ArtifactType => $node->artifactType(),
                $graph->nodes(),
            ),
        );
    }

    public function testPreservesRelationOrderForEdges(): void
    {
        $transcript = $this->createArtifact(
            '550e8400-e29b-41d4-a716-446655440001',
            ArtifactType::Transcript,
        );
        $summary = $this->createArtifact(
            '550e8400-e29b-41d4-a716-446655440002',
            ArtifactType::Summary,
        );
        $quiz = $this->createArtifact(
            '550e8400-e29b-41d4-a716-446655440003',
            ArtifactType::Quiz,
        );
        $relations = new ArtifactRelationCollection([
            new ArtifactRelation(
                $summary->id(),
                $transcript->id(),
                ArtifactRelationType::DerivedFrom,
            ),
            new ArtifactRelation(
                $quiz->id(),
                $summary->id(),
                ArtifactRelationType::References,
            ),
        ]);

        $graph = KnowledgeGraphBuilder::build(
            [$transcript, $summary, $quiz],
            $relations,
        );

        self::assertSame(
            [
                ArtifactRelationType::DerivedFrom,
                ArtifactRelationType::References,
            ],
            array_map(
                static fn (GraphEdge $edge): ArtifactRelationType => $edge->relationType(),
                $graph->edges(),
            ),
        );
    }

    public function testDeduplicatesNodesByArtifactId(): void
    {
        $transcript = $this->createArtifact(
            '550e8400-e29b-41d4-a716-446655440001',
            ArtifactType::Transcript,
        );

        $graph = KnowledgeGraphBuilder::build(
            [$transcript, $transcript],
            ArtifactRelationCollection::empty(),
        );

        self::assertSame(1, $graph->nodeCount());
    }

    public function testDeduplicatesEdges(): void
    {
        $transcript = $this->createArtifact(
            '550e8400-e29b-41d4-a716-446655440001',
            ArtifactType::Transcript,
        );
        $summary = $this->createArtifact(
            '550e8400-e29b-41d4-a716-446655440002',
            ArtifactType::Summary,
        );
        $relation = new ArtifactRelation(
            $summary->id(),
            $transcript->id(),
            ArtifactRelationType::DerivedFrom,
        );
        $relations = new ArtifactRelationCollection([$relation, $relation]);

        $graph = KnowledgeGraphBuilder::build([$transcript, $summary], $relations);

        self::assertSame(1, $graph->edgeCount());
    }

    public function testSkipsEdgesWhoseEndpointsAreNotPresentInNodes(): void
    {
        $summary = $this->createArtifact(
            '550e8400-e29b-41d4-a716-446655440002',
            ArtifactType::Summary,
        );
        $relations = new ArtifactRelationCollection([
            new ArtifactRelation(
                $summary->id(),
                new ArtifactId('550e8400-e29b-41d4-a716-446655440099'),
                ArtifactRelationType::DerivedFrom,
            ),
        ]);

        $graph = KnowledgeGraphBuilder::build([$summary], $relations);

        self::assertSame(1, $graph->nodeCount());
        self::assertSame(0, $graph->edgeCount());
    }

    public function testBuildsGraphFromResolvedRelations(): void
    {
        $transcript = $this->createArtifact(
            '550e8400-e29b-41d4-a716-446655440001',
            ArtifactType::Transcript,
        );
        $summary = $this->createArtifact(
            '550e8400-e29b-41d4-a716-446655440002',
            ArtifactType::Summary,
        );
        $quiz = $this->createArtifact(
            '550e8400-e29b-41d4-a716-446655440003',
            ArtifactType::Quiz,
        );
        $artifacts = [$transcript, $summary, $quiz];
        $relations = ArtifactRelationResolver::resolve($artifacts);

        $graph = KnowledgeGraphBuilder::build($artifacts, $relations);

        self::assertSame(3, $graph->nodeCount());
        self::assertGreaterThan(0, $graph->edgeCount());
        self::assertTrue($this->containsEdge(
            $graph->edges(),
            $summary->id()->value,
            $transcript->id()->value,
            ArtifactRelationType::DerivedFrom,
        ));
        self::assertTrue($this->containsEdge(
            $graph->edges(),
            $quiz->id()->value,
            $summary->id()->value,
            ArtifactRelationType::References,
        ));
    }

    /**
     * @param list<GraphEdge> $edges
     */
    private function containsEdge(
        array $edges,
        string $sourceId,
        string $targetId,
        ArtifactRelationType $type,
    ): bool {
        foreach ($edges as $edge) {
            if (
                $edge->sourceArtifactId()->value === $sourceId
                && $edge->targetArtifactId()->value === $targetId
                && $edge->relationType() === $type
            ) {
                return true;
            }
        }

        return false;
    }

    private function createArtifact(string $id, ArtifactType $type): Artifact
    {
        return Artifact::create(
            new ArtifactId($id),
            new ContentId('7c9e6679-7425-40de-944b-e07fc1f90ae7'),
            new ProcessingJobId('6ba7b810-9dad-11d1-80b4-00c04fd430c8'),
            $type,
            ArtifactContent::fromString('Sample content'),
        );
    }
}
