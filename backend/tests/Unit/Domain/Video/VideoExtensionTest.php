<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Video;

use App\Domain\Video\Exception\InvalidVideoJobException;
use App\Domain\Video\VideoExtension;
use PHPUnit\Framework\TestCase;

final class VideoExtensionTest extends TestCase
{
    public function testAcceptsSupportedExtensions(): void
    {
        self::assertSame(VideoExtension::Mp4, VideoExtension::fromFilename('lecture.mp4'));
        self::assertSame(VideoExtension::Mov, VideoExtension::fromFilename('clip.MOV'));
        self::assertSame(VideoExtension::Mkv, VideoExtension::fromFilename('episode.mkv'));
    }

    public function testRejectsUnsupportedExtension(): void
    {
        $this->expectException(InvalidVideoJobException::class);
        $this->expectExceptionMessage('Video upload must use one of the supported formats: mp4, mov, mkv.');

        VideoExtension::fromFilename('lecture.avi');
    }
}
