<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Video;

use App\Application\Video\Handlers\StreamUploadedVideoHandler;
use App\Domain\Video\Exception\InvalidVideoJobException;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoLanguage;
use App\Domain\Video\VideoJob;
use App\Domain\Video\VideoRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class StreamUploadedVideoHandlerTest extends TestCase
{
    public function testReturnsStoragePathWhenVideoFileExists(): void
    {
        $path = sys_get_temp_dir() . '/stream-uploaded-video-test.mp4';
        file_put_contents($path, 'fake-video');

        try {
            $videoId = VideoId::generate();
            $job = VideoJob::createUploaded($videoId, 'clip.mp4', VideoLanguage::English)
                ->withStoragePath($path);

            $repository = $this->createStub(VideoRepositoryInterface::class);
            $repository->method('findById')->willReturn($job);

            $handler = new StreamUploadedVideoHandler($repository);

            self::assertSame($path, $handler($videoId->value));
        } finally {
            @unlink($path);
        }
    }

    public function testThrowsWhenVideoMissingOnDisk(): void
    {
        $videoId = VideoId::generate();
        $job = VideoJob::createUploaded($videoId, 'clip.mp4', VideoLanguage::English)
            ->withStoragePath('/tmp/missing-video.mp4');

        $repository = $this->createStub(VideoRepositoryInterface::class);
        $repository->method('findById')->willReturn($job);

        $handler = new StreamUploadedVideoHandler($repository);

        $this->expectException(InvalidVideoJobException::class);
        $handler($videoId->value);
    }
}
