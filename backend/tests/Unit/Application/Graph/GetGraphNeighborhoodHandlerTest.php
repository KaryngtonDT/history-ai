<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Graph;

use App\Application\Graph\Handlers\GetGraphNeighborhoodHandler;
use App\Application\Graph\Queries\GetGraphNeighborhoodQuery;
use App\Domain\Artifact\Artifact;
use App\Domain\Artifact\ArtifactContent;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Content\ContentId;
use App\Domain\Content\Exception\InvalidContentIdException;
use App\Domain\Processing\ProcessingJobId;
use PHPUnit\Framework\TestCase;

final class GetGraphNeighborhoodHandlerTest extends TestCase
{
    public function testReturnsNullWhenArtifactIsNotInGraph(): void
    {
        $contentId = ContentId::generate();
        $unknownArtifactId = '550e8400-e29b-41d4-a716-446655440099';

        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('findByContentId')
            ->willReturn([
                $this->createArtifact(
                    '550e8400-e29b-41d4-a716-446655440001',
                    $contentId,
                    ArtifactType::Transcript,
                ),
            ]);

        $handler = new GetGraphNeighborhoodHandler($repository);
        $result = $handler(new GetGraphNeighborhoodQuery($contentId->value, $unknownArtifactId));

        self::assertNull($result);
    }

    public function testReturnsNullWhenContentHasNoArtifacts(): void
    {
        $contentId = ContentId::generate();
        $artifactId = '550e8400-e29b-41d4-a716-446655440001';

        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('findByContentId')
            ->willReturn([]);

        $handler = new GetGraphNeighborhoodHandler($repository);
        $result = $handler(new GetGraphNeighborhoodQuery($contentId->value, $artifactId));

        self::assertNull($result);
    }

    public function testReturnsDirectNeighborsForCenterArtifact(): void
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

        $handler = new GetGraphNeighborhoodHandler($repository);
        $result = $handler(new GetGraphNeighborhoodQuery($contentId->value, $summaryId));

        self::assertNotNull($result);
        self::assertSame($summaryId, $result->center->artifactId);
        self::assertSame('summary', $result->center->type);
        self::assertSame('Summary', $result->center->title);
        self::assertSame(
            [$transcriptId, $quizId],
            array_map(static fn (object $node): string => $node->artifactId, $result->neighbors),
        );
        self::assertCount(2, $result->edges);
        self::assertTrue($this->containsEdge($result->edges, $summaryId, $transcriptId, 'derived_from'));
        self::assertTrue($this->containsEdge($result->edges, $quizId, $summaryId, 'references'));
    }

    public function testIsolatedNodeReturnsEmptyNeighborhood(): void
    {
        $contentId = ContentId::generate();
        $transcriptId = '550e8400-e29b-41d4-a716-446655440001';

        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('findByContentId')
            ->willReturn([
                $this->createArtifact($transcriptId, $contentId, ArtifactType::Transcript),
            ]);

        $handler = new GetGraphNeighborhoodHandler($repository);
        $result = $handler(new GetGraphNeighborhoodQuery($contentId->value, $transcriptId));

        self::assertNotNull($result);
        self::assertSame($transcriptId, $result->center->artifactId);
        self::assertSame([], $result->neighbors);
        self::assertSame([], $result->edges);
    }

    public function testInvalidContentIdIsRejected(): void
    {
        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository->expects(self::never())->method('findByContentId');

        $handler = new GetGraphNeighborhoodHandler($repository);

        $this->expectException(InvalidContentIdException::class);

        $handler(new GetGraphNeighborhoodQuery('not-a-valid-uuid', '550e8400-e29b-41d4-a716-446655440001'));
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
}
