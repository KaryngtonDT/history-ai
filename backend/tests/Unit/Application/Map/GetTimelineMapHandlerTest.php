<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Map;

use App\Application\Map\Handlers\GetTimelineMapHandler;
use App\Application\Map\Queries\GetTimelineMapQuery;
use App\Domain\Artifact\Artifact;
use App\Domain\Artifact\ArtifactContent;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Artifact\Exception\InvalidArtifactException;
use App\Domain\Content\ContentId;
use App\Domain\Processing\ProcessingJobId;
use PHPUnit\Framework\TestCase;

final class GetTimelineMapHandlerTest extends TestCase
{
    public function testReturnsResolvedPlacesFromTimelineArtifact(): void
    {
        $artifactId = ArtifactId::generate();
        $timelineMarkdown = implode("\n", [
            '# Timeline',
            '',
            '## Ancient Rome',
            '- 753 BC — Foundation of Rome',
            '- Trade with Athens',
        ]);

        $artifact = Artifact::create(
            $artifactId,
            ContentId::generate(),
            ProcessingJobId::generate(),
            ArtifactType::Timeline,
            ArtifactContent::fromString($timelineMarkdown),
        );

        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('findById')
            ->with(self::callback(static fn (ArtifactId $id): bool => $id->equals($artifactId)))
            ->willReturn($artifact);

        $handler = new GetTimelineMapHandler($repository);
        $result = $handler(new GetTimelineMapQuery($artifactId->value));

        self::assertNotNull($result);
        self::assertCount(2, $result->places);
        self::assertSame('Rome', $result->places[0]->name);
        self::assertSame(41.9028, $result->places[0]->coordinates->latitude);
        self::assertSame(12.4964, $result->places[0]->coordinates->longitude);
        self::assertSame('753 BC — Foundation of Rome', $result->places[0]->description);
        self::assertSame('Athens', $result->places[1]->name);
    }

    public function testReturnsEmptyPlacesForTimelineWithoutKnownLocations(): void
    {
        $artifactId = ArtifactId::generate();
        $artifact = Artifact::create(
            $artifactId,
            ContentId::generate(),
            ProcessingJobId::generate(),
            ArtifactType::Timeline,
            ArtifactContent::fromString(
                implode("\n", ['## Events', '- Battle of Teutoburg Forest']),
            ),
        );

        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('findById')
            ->willReturn($artifact);

        $handler = new GetTimelineMapHandler($repository);
        $result = $handler(new GetTimelineMapQuery($artifactId->value));

        self::assertNotNull($result);
        self::assertSame([], $result->places);
    }

    public function testReturnsNullWhenArtifactDoesNotExist(): void
    {
        $artifactId = ArtifactId::generate();

        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('findById')
            ->willReturn(null);

        $handler = new GetTimelineMapHandler($repository);
        $result = $handler(new GetTimelineMapQuery($artifactId->value));

        self::assertNull($result);
    }

    public function testReturnsNullWhenArtifactIsNotTimelineType(): void
    {
        $artifactId = ArtifactId::generate();
        $artifact = Artifact::create(
            $artifactId,
            ContentId::generate(),
            ProcessingJobId::generate(),
            ArtifactType::Summary,
            ArtifactContent::fromString('Summary text'),
        );

        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('findById')
            ->willReturn($artifact);

        $handler = new GetTimelineMapHandler($repository);
        $result = $handler(new GetTimelineMapQuery($artifactId->value));

        self::assertNull($result);
    }

    public function testInvalidArtifactIdIsRejected(): void
    {
        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository->expects(self::never())->method('findById');

        $handler = new GetTimelineMapHandler($repository);

        $this->expectException(InvalidArtifactException::class);

        $handler(new GetTimelineMapQuery('not-a-valid-uuid'));
    }
}
