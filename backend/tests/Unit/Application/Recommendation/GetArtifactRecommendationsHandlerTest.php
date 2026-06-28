<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Recommendation;

use App\Application\Recommendation\Handlers\GetArtifactRecommendationsHandler;
use App\Application\Recommendation\Queries\GetArtifactRecommendationsQuery;
use App\Domain\Artifact\Artifact;
use App\Domain\Artifact\ArtifactContent;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Artifact\Exception\InvalidArtifactException;
use App\Domain\Content\ContentId;
use App\Domain\Content\Exception\InvalidContentIdException;
use App\Domain\Processing\ProcessingJobId;
use App\Domain\Recommendation\RecommendationEngine;
use PHPUnit\Framework\TestCase;

final class GetArtifactRecommendationsHandlerTest extends TestCase
{
    public function testReturnsEmptyRecommendationsWhenNoArtifactsExist(): void
    {
        $contentId = ContentId::generate();
        $artifactId = '550e8400-e29b-41d4-a716-446655440002';

        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('findByContentId')
            ->with(self::callback(static fn (ContentId $id): bool => $id->equals($contentId)))
            ->willReturn([]);

        $handler = new GetArtifactRecommendationsHandler($repository, new RecommendationEngine());
        $result = $handler(new GetArtifactRecommendationsQuery($contentId->value, $artifactId));

        self::assertSame([], $result->recommendations);
    }

    public function testReturnsDirectNeighboursForCurrentArtifact(): void
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

        $handler = new GetArtifactRecommendationsHandler($repository, new RecommendationEngine());
        $result = $handler(new GetArtifactRecommendationsQuery($contentId->value, $summaryId));

        self::assertSame(2, count($result->recommendations));
        self::assertSame(
            [$transcriptId, $quizId],
            array_map(static fn (object $item): string => $item->artifactId, $result->recommendations),
        );
        self::assertSame(
            ['Transcript', 'Quiz'],
            array_map(static fn (object $item): string => $item->title, $result->recommendations),
        );
        self::assertSame(
            ['derived_from', 'references'],
            array_map(static fn (object $item): string => $item->reason, $result->recommendations),
        );
        self::assertSame(
            count($result->recommendations),
            count($this->uniqueRecommendations($result->recommendations)),
        );

        foreach ($result->recommendations as $recommendation) {
            self::assertNotSame($summaryId, $recommendation->artifactId);
        }
    }

    public function testReturnsEmptyRecommendationsWhenCurrentArtifactIsUnknown(): void
    {
        $contentId = ContentId::generate();
        $transcriptId = '550e8400-e29b-41d4-a716-446655440001';
        $summaryId = '550e8400-e29b-41d4-a716-446655440002';
        $unknownId = '550e8400-e29b-41d4-a716-446655440099';

        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('findByContentId')
            ->willReturn([
                $this->createArtifact($transcriptId, $contentId, ArtifactType::Transcript),
                $this->createArtifact($summaryId, $contentId, ArtifactType::Summary),
            ]);

        $handler = new GetArtifactRecommendationsHandler($repository, new RecommendationEngine());
        $result = $handler(new GetArtifactRecommendationsQuery($contentId->value, $unknownId));

        self::assertSame([], $result->recommendations);
    }

    public function testInvalidContentIdIsRejected(): void
    {
        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository->expects(self::never())->method('findByContentId');

        $handler = new GetArtifactRecommendationsHandler($repository, new RecommendationEngine());

        $this->expectException(InvalidContentIdException::class);

        $handler(new GetArtifactRecommendationsQuery(
            'not-a-valid-uuid',
            '550e8400-e29b-41d4-a716-446655440002',
        ));
    }

    public function testInvalidArtifactIdIsRejected(): void
    {
        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository->expects(self::never())->method('findByContentId');

        $handler = new GetArtifactRecommendationsHandler($repository, new RecommendationEngine());

        $this->expectException(InvalidArtifactException::class);

        $handler(new GetArtifactRecommendationsQuery(
            ContentId::generate()->value,
            'not-a-valid-uuid',
        ));
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
     * @param list<object{artifactId: string, type: string, title: string, reason: string}> $recommendations
     *
     * @return list<string>
     */
    private function uniqueRecommendations(array $recommendations): array
    {
        return array_values(array_unique(array_map(
            static fn (object $recommendation): string => $recommendation->artifactId,
            $recommendations,
        )));
    }
}
