<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Source;

use App\Domain\Source\AudioExtension;
use App\Domain\Source\Exception\InvalidSourceException;
use App\Domain\Source\Exception\InvalidSourceIdException;
use App\Domain\Source\Source;
use App\Domain\Source\SourceId;
use App\Domain\Source\SourceMetadata;
use App\Domain\Source\SourceStatus;
use App\Domain\Source\SourceType;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class SourceTest extends TestCase
{
    private const string SOURCE_ID = '550e8400-e29b-41d4-a716-446655440000';

    public function testCreateUploadedInitializesUploadedStatus(): void
    {
        $createdAt = new DateTimeImmutable('2026-07-01T12:00:00+00:00');
        $source = Source::createUploaded(
            new SourceId(self::SOURCE_ID),
            SourceType::Audio,
            new SourceMetadata(' podcast.mp3 '),
            $createdAt,
        );

        self::assertTrue($source->id()->equals(new SourceId(self::SOURCE_ID)));
        self::assertSame(SourceType::Audio, $source->type());
        self::assertSame('podcast.mp3', $source->metadata()->originalFilename);
        self::assertSame('podcast', $source->metadata()->displayTitle());
        self::assertSame(SourceStatus::Uploaded, $source->status());
        self::assertSame($createdAt, $source->createdAt());
    }

    public function testLifecycleTransitionsReturnNewInstances(): void
    {
        $uploaded = Source::createUploaded(
            new SourceId(self::SOURCE_ID),
            SourceType::Audio,
            new SourceMetadata('podcast.mp3'),
        );

        $stored = $uploaded->withStoragePath('/var/audio-storage/podcast.mp3');
        $queued = $stored->queue();
        $processing = $queued->startProcessing();
        $completed = $processing->complete();

        self::assertSame(SourceStatus::Uploaded, $uploaded->status());
        self::assertSame('/var/audio-storage/podcast.mp3', $stored->storagePath());
        self::assertSame(SourceStatus::Queued, $queued->status());
        self::assertSame(SourceStatus::Processing, $processing->status());
        self::assertSame(SourceStatus::Completed, $completed->status());
        self::assertNotSame($uploaded, $stored);
    }

    public function testFailTransitionFromProcessing(): void
    {
        $failed = Source::createUploaded(
            new SourceId(self::SOURCE_ID),
            SourceType::Audio,
            new SourceMetadata('podcast.mp3'),
        )
            ->withStoragePath('/var/audio-storage/podcast.mp3')
            ->queue()
            ->startProcessing()
            ->fail();

        self::assertSame(SourceStatus::Failed, $failed->status());
    }

    public function testRejectsInvalidStatusTransitions(): void
    {
        $uploaded = Source::createUploaded(
            new SourceId(self::SOURCE_ID),
            SourceType::Audio,
            new SourceMetadata('podcast.mp3'),
        );

        $this->expectException(InvalidSourceException::class);
        $this->expectExceptionMessage('Cannot start processing a source in status "uploaded".');

        $uploaded->startProcessing();
    }

    public function testRejectsQueueWithoutStoragePath(): void
    {
        $uploaded = Source::createUploaded(
            new SourceId(self::SOURCE_ID),
            SourceType::Audio,
            new SourceMetadata('podcast.mp3'),
        );

        $this->expectException(InvalidSourceException::class);
        $this->expectExceptionMessage('Source must be stored before it can be queued.');

        $uploaded->queue();
    }
}
