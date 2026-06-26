<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Artifact;

use App\Domain\Artifact\Artifact;
use App\Domain\Artifact\ArtifactContent;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Artifact\Exception\InvalidArtifactException;
use App\Domain\Content\ContentId;
use App\Domain\Content\Exception\InvalidContentIdException;
use App\Domain\Processing\Exception\InvalidProcessingJobException;
use App\Domain\Processing\ProcessingJobId;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ArtifactTest extends TestCase
{
    public function testCreateValidArtifact(): void
    {
        $artifactId = ArtifactId::generate();
        $contentId = ContentId::generate();
        $processingJobId = ProcessingJobId::generate();
        $content = ArtifactContent::fromString('Extracted text from the PDF.');

        $artifact = Artifact::create(
            $artifactId,
            $contentId,
            $processingJobId,
            ArtifactType::Summary,
            $content,
        );

        self::assertTrue($artifact->id()->equals($artifactId));
        self::assertTrue($artifact->contentId()->equals($contentId));
        self::assertTrue($artifact->processingJobId()->equals($processingJobId));
        self::assertSame(ArtifactType::Summary, $artifact->type());
        self::assertTrue($artifact->content()->equals($content));
        self::assertSame('Extracted text from the PDF.', $artifact->content()->value());
        self::assertLessThanOrEqual(new \DateTimeImmutable(), $artifact->createdAt());
    }

    #[DataProvider('artifactTypeProvider')]
    public function testSupportsAllArtifactTypes(ArtifactType $type): void
    {
        $artifact = Artifact::create(
            ArtifactId::generate(),
            ContentId::generate(),
            ProcessingJobId::generate(),
            $type,
            ArtifactContent::fromString('Sample output'),
        );

        self::assertSame($type, $artifact->type());
    }

    /**
     * @return iterable<string, array{ArtifactType}>
     */
    public static function artifactTypeProvider(): iterable
    {
        yield 'summary' => [ArtifactType::Summary];
        yield 'quiz' => [ArtifactType::Quiz];
        yield 'flashcards' => [ArtifactType::Flashcards];
        yield 'podcast' => [ArtifactType::Podcast];
        yield 'timeline' => [ArtifactType::Timeline];
        yield 'transcript' => [ArtifactType::Transcript];
    }

    #[DataProvider('emptyContentProvider')]
    public function testEmptyContentIsRejected(string $value): void
    {
        $this->expectException(InvalidArtifactException::class);
        $this->expectExceptionMessage('Artifact content cannot be empty.');

        ArtifactContent::fromString($value);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function emptyContentProvider(): iterable
    {
        yield 'empty string' => [''];
        yield 'whitespace only' => ['   '];
        yield 'tab and newline' => ["\t\n"];
    }

    public function testInvalidArtifactIdIsRejected(): void
    {
        $this->expectException(InvalidArtifactException::class);
        $this->expectExceptionMessage('Artifact id must be a valid UUID.');

        new ArtifactId('not-a-uuid');
    }

    public function testInvalidContentIdIsRejected(): void
    {
        $this->expectException(InvalidContentIdException::class);

        Artifact::create(
            ArtifactId::generate(),
            new ContentId('invalid-content-id'),
            ProcessingJobId::generate(),
            ArtifactType::Summary,
            ArtifactContent::fromString('Valid content'),
        );
    }

    public function testInvalidProcessingJobIdIsRejected(): void
    {
        $this->expectException(InvalidProcessingJobException::class);

        Artifact::create(
            ArtifactId::generate(),
            ContentId::generate(),
            new ProcessingJobId('invalid-job-id'),
            ArtifactType::Summary,
            ArtifactContent::fromString('Valid content'),
        );
    }

    public function testReconstitutePreservesPersistedState(): void
    {
        $createdAt = new \DateTimeImmutable('2026-06-26 12:00:00');
        $artifactId = ArtifactId::generate();
        $contentId = ContentId::generate();
        $processingJobId = ProcessingJobId::generate();
        $content = ArtifactContent::fromString('Persisted artifact body');

        $artifact = Artifact::reconstitute(
            $artifactId,
            $contentId,
            $processingJobId,
            ArtifactType::Transcript,
            $content,
            $createdAt,
        );

        self::assertSame($createdAt, $artifact->createdAt());
        self::assertSame(ArtifactType::Transcript, $artifact->type());
        self::assertSame('Persisted artifact body', $artifact->content()->value());
    }
}
