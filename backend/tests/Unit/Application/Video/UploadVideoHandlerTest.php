<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Video;

use App\Application\Video\Commands\UploadVideoCommand;
use App\Application\Video\Handlers\UploadVideoHandler;
use App\Application\Video\Ports\VideoProcessingQueueInterface;
use App\Application\Video\Ports\VideoStorageInterface;
use App\Domain\Video\Exception\InvalidVideoJobException;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoJob;
use App\Domain\Video\VideoRepositoryInterface;
use App\Domain\Video\VideoStatus;
use PHPUnit\Framework\TestCase;

final class UploadVideoHandlerTest extends TestCase
{
    public function testStoresPersistsAndQueuesVideoJob(): void
    {
        $temporaryPath = tempnam(sys_get_temp_dir(), 'video-upload-');
        self::assertNotFalse($temporaryPath);
        file_put_contents($temporaryPath, str_repeat('a', 128));

        $videoStorage = $this->createMock(VideoStorageInterface::class);
        $videoRepository = $this->createMock(VideoRepositoryInterface::class);
        $videoProcessingQueue = $this->createMock(VideoProcessingQueueInterface::class);

        $videoStorage
            ->expects(self::once())
            ->method('store')
            ->willReturnCallback(static function (VideoId $videoId) use ($temporaryPath): string {
                return sprintf('/var/video-storage/%s.mp4', $videoId->value);
            });

        $videoRepository
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(static function (VideoJob $job): bool {
                return VideoStatus::Queued === $job->status()
                    && str_ends_with($job->storagePath() ?? '', '.mp4');
            }));

        $videoProcessingQueue
            ->expects(self::once())
            ->method('enqueue')
            ->with(self::isInstanceOf(VideoId::class));

        $handler = new UploadVideoHandler(
            maxUploadBytes: 1024,
            videoStorage: $videoStorage,
            videoRepository: $videoRepository,
            videoProcessingQueue: $videoProcessingQueue,
        );

        $result = ($handler)(new UploadVideoCommand(
            originalFilename: 'lecture.mp4',
            fileSizeBytes: 512,
            temporaryPath: $temporaryPath,
        ));

        self::assertTrue(VideoId::isValid($result->videoId->value));
        self::assertSame(VideoStatus::Queued, $result->status);

        @unlink($temporaryPath);
    }

    public function testRejectsUnsupportedFormat(): void
    {
        $handler = new UploadVideoHandler(
            maxUploadBytes: 1024,
            videoStorage: $this->createStub(VideoStorageInterface::class),
            videoRepository: $this->createStub(VideoRepositoryInterface::class),
            videoProcessingQueue: $this->createStub(VideoProcessingQueueInterface::class),
        );

        $this->expectException(InvalidVideoJobException::class);

        ($handler)(new UploadVideoCommand(
            originalFilename: 'lecture.avi',
            fileSizeBytes: 512,
            temporaryPath: tempnam(sys_get_temp_dir(), 'video-upload-') ?: '',
        ));
    }

    public function testRejectsOversizedUpload(): void
    {
        $handler = new UploadVideoHandler(
            maxUploadBytes: 1024,
            videoStorage: $this->createStub(VideoStorageInterface::class),
            videoRepository: $this->createStub(VideoRepositoryInterface::class),
            videoProcessingQueue: $this->createStub(VideoProcessingQueueInterface::class),
        );

        $this->expectException(InvalidVideoJobException::class);

        ($handler)(new UploadVideoCommand(
            originalFilename: 'lecture.mp4',
            fileSizeBytes: 2048,
            temporaryPath: tempnam(sys_get_temp_dir(), 'video-upload-') ?: '',
        ));
    }
}
