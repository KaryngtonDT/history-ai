<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Artifact;

use App\Application\Artifact\Commands\CreateArtifactCommand;
use App\Application\Artifact\Handlers\CreateArtifactHandler;
use App\Domain\Artifact\Artifact;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Artifact\Exception\InvalidArtifactException;
use App\Domain\Content\ContentId;
use App\Domain\Content\Exception\InvalidContentIdException;
use App\Domain\Processing\Exception\InvalidProcessingJobException;
use App\Domain\Processing\ProcessingJobId;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CreateArtifactHandlerTest extends TestCase
{
    public function testCreatesArtifactAndReturnsResult(): void
    {
        $contentId = ContentId::generate();
        $processingJobId = ProcessingJobId::generate();
        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(static function (Artifact $artifact) use ($contentId, $processingJobId): bool {
                return $contentId->equals($artifact->contentId())
                    && $processingJobId->equals($artifact->processingJobId())
                    && ArtifactType::Summary === $artifact->type()
                    && 'Summary text produced by processing.' === $artifact->content()->value();
            }));

        $handler = new CreateArtifactHandler($repository);

        $result = $handler(new CreateArtifactCommand(
            contentId: $contentId->value,
            processingJobId: $processingJobId->value,
            artifactType: ArtifactType::Summary,
            artifactContent: 'Summary text produced by processing.',
        ));

        self::assertNotEmpty($result->artifactId->value);
        self::assertSame(ArtifactType::Summary, $result->type);
        self::assertInstanceOf(\DateTimeImmutable::class, $result->createdAt);
    }

    #[DataProvider('artifactTypeProvider')]
    public function testSupportsDifferentArtifactTypes(ArtifactType $type, string $content): void
    {
        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(static function (Artifact $artifact) use ($type, $content): bool {
                return $type === $artifact->type()
                    && $content === $artifact->content()->value();
            }));

        $handler = new CreateArtifactHandler($repository);

        $result = $handler(new CreateArtifactCommand(
            contentId: ContentId::generate()->value,
            processingJobId: ProcessingJobId::generate()->value,
            artifactType: $type,
            artifactContent: $content,
        ));

        self::assertSame($type, $result->type);
    }

    /**
     * @return iterable<string, array{ArtifactType, string}>
     */
    public static function artifactTypeProvider(): iterable
    {
        yield 'summary' => [ArtifactType::Summary, 'Summary output'];
        yield 'quiz' => [ArtifactType::Quiz, '{"questions":[]}'];
        yield 'flashcards' => [ArtifactType::Flashcards, '{"cards":[]}'];
        yield 'podcast' => [ArtifactType::Podcast, 'Podcast script'];
        yield 'timeline' => [ArtifactType::Timeline, '{"events":[]}'];
        yield 'transcript' => [ArtifactType::Transcript, 'Full transcript text'];
    }

    public function testRepositorySaveIsCalledExactlyOnce(): void
    {
        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository->expects(self::once())->method('save');

        $handler = new CreateArtifactHandler($repository);

        $handler(new CreateArtifactCommand(
            contentId: ContentId::generate()->value,
            processingJobId: ProcessingJobId::generate()->value,
            artifactType: ArtifactType::Transcript,
            artifactContent: 'Transcript body',
        ));
    }

    public function testInvalidContentIdIsRejected(): void
    {
        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository->expects(self::never())->method('save');

        $handler = new CreateArtifactHandler($repository);

        $this->expectException(InvalidContentIdException::class);

        $handler(new CreateArtifactCommand(
            contentId: 'not-a-valid-uuid',
            processingJobId: ProcessingJobId::generate()->value,
            artifactType: ArtifactType::Summary,
            artifactContent: 'Summary text',
        ));
    }

    public function testInvalidProcessingJobIdIsRejected(): void
    {
        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository->expects(self::never())->method('save');

        $handler = new CreateArtifactHandler($repository);

        $this->expectException(InvalidProcessingJobException::class);

        $handler(new CreateArtifactCommand(
            contentId: ContentId::generate()->value,
            processingJobId: 'not-a-valid-uuid',
            artifactType: ArtifactType::Summary,
            artifactContent: 'Summary text',
        ));
    }

    public function testEmptyArtifactContentIsRejected(): void
    {
        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository->expects(self::never())->method('save');

        $handler = new CreateArtifactHandler($repository);

        $this->expectException(InvalidArtifactException::class);

        $handler(new CreateArtifactCommand(
            contentId: ContentId::generate()->value,
            processingJobId: ProcessingJobId::generate()->value,
            artifactType: ArtifactType::Summary,
            artifactContent: '   ',
        ));
    }
}
