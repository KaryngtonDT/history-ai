<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Relation;

use App\Application\Relation\Handlers\GetArtifactRelationsHandler;
use App\Application\Relation\Queries\GetArtifactRelationsQuery;
use App\Domain\Artifact\Artifact;
use App\Domain\Artifact\ArtifactContent;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Content\ContentId;
use App\Domain\Content\Exception\InvalidContentIdException;
use App\Domain\Processing\ProcessingJobId;
use PHPUnit\Framework\TestCase;

final class GetArtifactRelationsHandlerTest extends TestCase
{
    public function testReturnsEmptyRelationsWhenNoArtifactsExist(): void
    {
        $contentId = ContentId::generate();

        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('findByContentId')
            ->with(self::callback(static fn (ContentId $id): bool => $id->equals($contentId)))
            ->willReturn([]);

        $handler = new GetArtifactRelationsHandler($repository);
        $result = $handler(new GetArtifactRelationsQuery($contentId->value));

        self::assertSame([], $result->relations);
    }

    public function testReturnsDeterministicRelationsForContentArtifacts(): void
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

        $handler = new GetArtifactRelationsHandler($repository);
        $result = $handler(new GetArtifactRelationsQuery($contentId->value));

        self::assertTrue($this->containsRelation($result->relations, $summaryId, $transcriptId, 'derived_from'));
        self::assertTrue($this->containsRelation($result->relations, $quizId, $summaryId, 'references'));
        self::assertSame(
            count($result->relations),
            count($this->uniqueRelations($result->relations)),
        );
    }

    public function testInvalidContentIdIsRejected(): void
    {
        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository->expects(self::never())->method('findByContentId');

        $handler = new GetArtifactRelationsHandler($repository);

        $this->expectException(InvalidContentIdException::class);

        $handler(new GetArtifactRelationsQuery('not-a-valid-uuid'));
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
     * @param list<object{sourceArtifactId: string, targetArtifactId: string, type: string}> $relations
     */
    private function containsRelation(
        array $relations,
        string $sourceId,
        string $targetId,
        string $type,
    ): bool {
        foreach ($relations as $relation) {
            if (
                $relation->sourceArtifactId === $sourceId
                && $relation->targetArtifactId === $targetId
                && $relation->type === $type
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<object{sourceArtifactId: string, targetArtifactId: string, type: string}> $relations
     *
     * @return list<string>
     */
    private function uniqueRelations(array $relations): array
    {
        return array_values(array_unique(array_map(
            static fn (object $relation): string => sprintf(
                '%s->%s:%s',
                $relation->sourceArtifactId,
                $relation->targetArtifactId,
                $relation->type,
            ),
            $relations,
        )));
    }
}
