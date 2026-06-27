<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Timeline;

use App\Application\Timeline\Handlers\GetTimelineHandler;
use App\Application\Timeline\Queries\GetTimelineQuery;
use App\Domain\Artifact\Artifact;
use App\Domain\Artifact\ArtifactContent;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Artifact\Exception\InvalidArtifactException;
use App\Domain\Content\ContentId;
use App\Domain\Processing\ProcessingJobId;
use PHPUnit\Framework\TestCase;

final class GetTimelineHandlerTest extends TestCase
{
    public function testReturnsStructuredTimelineFromTimelineArtifact(): void
    {
        $artifactId = ArtifactId::generate();
        $timelineMarkdown = implode("\n", [
            '# Timeline',
            '',
            '## Ancient Rome',
            '- 753 BC — Foundation of Rome',
            '- Republic established',
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

        $handler = new GetTimelineHandler($repository);
        $result = $handler(new GetTimelineQuery($artifactId->value));

        self::assertNotNull($result);
        self::assertCount(1, $result->sections);
        self::assertSame('Ancient Rome', $result->sections[0]->title);
        self::assertSame(
            ['753 BC — Foundation of Rome', 'Republic established'],
            array_map(
                static fn ($event): string => $event->text,
                $result->sections[0]->events,
            ),
        );
    }

    public function testReturnsNullWhenArtifactDoesNotExist(): void
    {
        $artifactId = ArtifactId::generate();

        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('findById')
            ->willReturn(null);

        $handler = new GetTimelineHandler($repository);
        $result = $handler(new GetTimelineQuery($artifactId->value));

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

        $handler = new GetTimelineHandler($repository);
        $result = $handler(new GetTimelineQuery($artifactId->value));

        self::assertNull($result);
    }

    public function testInvalidArtifactIdIsRejected(): void
    {
        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository->expects(self::never())->method('findById');

        $handler = new GetTimelineHandler($repository);

        $this->expectException(InvalidArtifactException::class);

        $handler(new GetTimelineQuery('not-a-valid-uuid'));
    }
}
