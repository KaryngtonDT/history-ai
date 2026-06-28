<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Graph;

use App\Application\Graph\Handlers\GetKnowledgeGraphHandler;
use App\Application\Graph\Queries\GetKnowledgeGraphQuery;
use App\Domain\Artifact\Artifact;
use App\Domain\Artifact\ArtifactContent;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Content\ContentId;
use App\Domain\Content\Exception\InvalidContentIdException;
use App\Domain\Processing\ProcessingJobId;
use PHPUnit\Framework\TestCase;

final class GetKnowledgeGraphHandlerTest extends TestCase
{
    public function testReturnsEmptyGraphWhenNoArtifactsExist(): void
    {
        $contentId = ContentId::generate();

        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('findByContentId')
            ->with(self::callback(static fn (ContentId $id): bool => $id->equals($contentId)))
            ->willReturn([]);

        $handler = new GetKnowledgeGraphHandler($repository);
        $result = $handler(new GetKnowledgeGraphQuery($contentId->value));

        self::assertSame([], $result->nodes);
        self::assertSame([], $result->edges);
    }

    public function testBuildsNodesFromArtifactsAndEdgesFromRelations(): void
    {
        $contentId = ContentId::generate();
        $transcriptId = '550e8400-e29b-41d4-a716-446655440001';
        $summaryId = '550e8400-e29b-41d4-a716-446655440002';
        $quizId = '550e8400-e29b-41d4-a716-446655440003';

        $artifacts = [
            $this->createArtifact($transcriptId, $contentId, ArtifactType::Transcript),
            $this->createArtifact($summaryId, $contentId, ArtifactType::Summary),
            $this->createArtifact($quizId, $contentId, ArtifactType::Quiz),
        ];

        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('findByContentId')
            ->willReturn($artifacts);

        $handler = new GetKnowledgeGraphHandler($repository);
        $result = $handler(new GetKnowledgeGraphQuery($contentId->value));

        self::assertSame(3, count($result->nodes));
        self::assertSame(
            ['transcript', 'summary', 'quiz'],
            array_map(static fn (object $node): string => $node->type, $result->nodes),
        );
        self::assertSame(
            ['Transcript', 'Summary', 'Quiz'],
            array_map(static fn (object $node): string => $node->title, $result->nodes),
        );
        self::assertTrue($this->containsEdge($result->edges, $summaryId, $transcriptId, 'derived_from'));
        self::assertTrue($this->containsEdge($result->edges, $quizId, $summaryId, 'references'));
        self::assertSame(
            count($result->nodes),
            count($this->uniqueNodes($result->nodes)),
        );
        self::assertSame(
            count($result->edges),
            count($this->uniqueEdges($result->edges)),
        );
    }

    public function testInvalidContentIdIsRejected(): void
    {
        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository->expects(self::never())->method('findByContentId');

        $handler = new GetKnowledgeGraphHandler($repository);

        $this->expectException(InvalidContentIdException::class);

        $handler(new GetKnowledgeGraphQuery('not-a-valid-uuid'));
    }

    private function createArtifact(string $id, ContentId $contentId, ArtifactType $type): Artifact
    {
        return Artifact::create(
            new ArtifactId($id),
            $contentId,
            ProcessingJobId::generate(),
            $type,
            ArtifactContent::fromString('content for ' . $type->value),
        );
    }

    /**
     * @param list<object{sourceArtifactId: string, targetArtifactId: string, type: string}> $edges
     */
    private function containsEdge(
        array $edges,
        string $sourceId,
        string $targetId,
        string $type,
    ): bool {
        foreach ($edges as $edge) {
            if (
                $edge->sourceArtifactId === $sourceId
                && $edge->targetArtifactId === $targetId
                && $edge->type === $type
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<object{artifactId: string, type: string, title: string}> $nodes
     *
     * @return list<string>
     */
    private function uniqueNodes(array $nodes): array
    {
        return array_values(array_unique(array_map(
            static fn (object $node): string => $node->artifactId,
            $nodes,
        )));
    }

    /**
     * @param list<object{sourceArtifactId: string, targetArtifactId: string, type: string}> $edges
     *
     * @return list<string>
     */
    private function uniqueEdges(array $edges): array
    {
        return array_values(array_unique(array_map(
            static fn (object $edge): string => sprintf(
                '%s->%s:%s',
                $edge->sourceArtifactId,
                $edge->targetArtifactId,
                $edge->type,
            ),
            $edges,
        )));
    }
}
