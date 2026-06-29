<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Video;

use App\Domain\Video\Exception\InvalidVideoJobException;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoJob;
use App\Domain\Video\VideoLanguage;
use App\Domain\Video\VideoStatus;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class VideoJobTest extends TestCase
{
    private const string VIDEO_ID = '550e8400-e29b-41d4-a716-446655440000';

    public function testCreateUploadedInitializesUploadedStatus(): void
    {
        $createdAt = new DateTimeImmutable('2026-06-26T12:00:00+00:00');
        $job = VideoJob::createUploaded(
            new VideoId(self::VIDEO_ID),
            ' lecture.mp4 ',
            VideoLanguage::English,
            $createdAt,
        );

        self::assertTrue($job->id()->equals(new VideoId(self::VIDEO_ID)));
        self::assertSame('lecture.mp4', $job->originalFilename());
        self::assertSame(VideoLanguage::English, $job->language());
        self::assertSame(VideoStatus::Uploaded, $job->status());
        self::assertSame($createdAt, $job->createdAt());
    }

    public function testLifecycleTransitionsReturnNewInstances(): void
    {
        $uploaded = VideoJob::createUploaded(
            new VideoId(self::VIDEO_ID),
            'lecture.mp4',
            VideoLanguage::French,
        );

        $stored = $uploaded->withStoragePath('/var/video-storage/lecture.mp4');
        $queued = $stored->queue();
        $processing = $queued->startProcessing();
        $completed = $processing->complete();

        self::assertSame(VideoStatus::Uploaded, $uploaded->status());
        self::assertSame('/var/video-storage/lecture.mp4', $stored->storagePath());
        self::assertSame(VideoStatus::Queued, $queued->status());
        self::assertSame(VideoStatus::Processing, $processing->status());
        self::assertSame(VideoStatus::Completed, $completed->status());
        self::assertNotSame($uploaded, $stored);
        self::assertNotSame($stored, $queued);
        self::assertNotSame($queued, $processing);
        self::assertNotSame($processing, $completed);
    }

    public function testFailTransitionFromProcessing(): void
    {
        $failed = VideoJob::createUploaded(
            new VideoId(self::VIDEO_ID),
            'lecture.mp4',
            VideoLanguage::German,
        )
            ->withStoragePath('/var/video-storage/lecture.mp4')
            ->queue()
            ->startProcessing()
            ->fail();

        self::assertSame(VideoStatus::Failed, $failed->status());
    }

    public function testRejectsInvalidStatusTransitions(): void
    {
        $uploaded = VideoJob::createUploaded(
            new VideoId(self::VIDEO_ID),
            'lecture.mp4',
            VideoLanguage::Unknown,
        );

        $this->expectException(InvalidVideoJobException::class);
        $this->expectExceptionMessage('Cannot start processing a video job in status "uploaded".');

        $uploaded->startProcessing();
    }

    public function testRejectsQueueWithoutStoragePath(): void
    {
        $uploaded = VideoJob::createUploaded(
            new VideoId(self::VIDEO_ID),
            'lecture.mp4',
            VideoLanguage::English,
        );

        $this->expectException(InvalidVideoJobException::class);
        $this->expectExceptionMessage('Video job must be stored before it can be queued.');

        $uploaded->queue();
    }

    public function testRejectsEmptyFilename(): void
    {
        $this->expectException(InvalidVideoJobException::class);
        $this->expectExceptionMessage('Video original filename cannot be empty.');

        VideoJob::createUploaded(
            new VideoId(self::VIDEO_ID),
            '   ',
            VideoLanguage::English,
        );
    }

    public function testRejectsFilenameWithPathSeparators(): void
    {
        $this->expectException(InvalidVideoJobException::class);
        $this->expectExceptionMessage('Video original filename cannot contain path separators.');

        VideoJob::createUploaded(
            new VideoId(self::VIDEO_ID),
            '../lecture.mp4',
            VideoLanguage::English,
        );
    }

    public function testRejectsFilenameWithNullBytes(): void
    {
        $this->expectException(InvalidVideoJobException::class);
        $this->expectExceptionMessage('Video original filename cannot contain null bytes.');

        VideoJob::createUploaded(
            new VideoId(self::VIDEO_ID),
            "lecture\0.mp4",
            VideoLanguage::English,
        );
    }
}
