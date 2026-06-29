<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Video;

use App\Application\Video\Commands\UploadVideoCommand;
use App\Application\Video\Handlers\UploadVideoHandler;
use App\Domain\Video\Exception\InvalidVideoJobException;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoStatus;
use PHPUnit\Framework\TestCase;

final class UploadVideoHandlerTest extends TestCase
{
    public function testCreatesUploadedVideoJob(): void
    {
        $handler = new UploadVideoHandler(maxUploadBytes: 1024);

        $result = ($handler)(new UploadVideoCommand(
            originalFilename: 'lecture.mp4',
            fileSizeBytes: 512,
        ));

        self::assertTrue(VideoId::isValid($result->videoId->value));
        self::assertSame(VideoStatus::Uploaded, $result->status);
    }

    public function testRejectsUnsupportedFormat(): void
    {
        $handler = new UploadVideoHandler(maxUploadBytes: 1024);

        $this->expectException(InvalidVideoJobException::class);

        ($handler)(new UploadVideoCommand(
            originalFilename: 'lecture.avi',
            fileSizeBytes: 512,
        ));
    }

    public function testRejectsOversizedUpload(): void
    {
        $handler = new UploadVideoHandler(maxUploadBytes: 1024);

        $this->expectException(InvalidVideoJobException::class);

        ($handler)(new UploadVideoCommand(
            originalFilename: 'lecture.mp4',
            fileSizeBytes: 2048,
        ));
    }
}
