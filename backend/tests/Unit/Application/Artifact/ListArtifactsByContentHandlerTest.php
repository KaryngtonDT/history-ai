<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Artifact;

use App\Application\Artifact\Handlers\ListArtifactsByContentHandler;
use App\Application\Artifact\Queries\ListArtifactsByContentQuery;
use App\Domain\Artifact\Artifact;
use App\Domain\Artifact\ArtifactContent;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Content\ContentId;
use App\Domain\Content\Exception\InvalidContentIdException;
use App\Domain\Processing\ProcessingJobId;
use PHPUnit\Framework\TestCase;

final class ListArtifactsByContentHandlerTest extends TestCase
{
    public function testReturnsArtifactsForContent(): void
    {
        $contentId = ContentId::generate();
        $summary = Artifact::create(
            ArtifactId::generate(),
            $contentId,
            ProcessingJobId::generate(),
            ArtifactType::Summary,
            ArtifactContent::fromString('Summary output'),
        );
        $quiz = Artifact::create(
            ArtifactId::generate(),
            $contentId,
            ProcessingJobId::generate(),
            ArtifactType::Quiz,
            ArtifactContent::fromString('Quiz output'),
        );

        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('findByContentId')
            ->with(self::callback(static fn (ContentId $id): bool => $id->equals($contentId)))
            ->willReturn([$summary, $quiz]);

        $handler = new ListArtifactsByContentHandler($repository);

        $result = $handler(new ListArtifactsByContentQuery($contentId->value));

        self::assertCount(2, $result->items);
        self::assertSame($summary->id()->value, $result->items[0]->id);
        self::assertSame($contentId->value, $result->items[0]->contentId);
        self::assertSame('summary', $result->items[0]->type);
        self::assertSame('Summary output', $result->items[0]->content);
        self::assertSame('quiz', $result->items[1]->type);
    }

    public function testReturnsEmptyListWhenNoArtifactsExist(): void
    {
        $contentId = ContentId::generate();

        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('findByContentId')
            ->willReturn([]);

        $handler = new ListArtifactsByContentHandler($repository);

        $result = $handler(new ListArtifactsByContentQuery($contentId->value));

        self::assertSame([], $result->items);
    }

    public function testInvalidContentIdIsRejected(): void
    {
        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository->expects(self::never())->method('findByContentId');

        $handler = new ListArtifactsByContentHandler($repository);

        $this->expectException(InvalidContentIdException::class);

        $handler(new ListArtifactsByContentQuery('not-a-valid-uuid'));
    }
}
