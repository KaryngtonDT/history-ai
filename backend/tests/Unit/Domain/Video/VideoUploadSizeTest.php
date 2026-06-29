<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Video;

use App\Domain\Video\Exception\InvalidVideoJobException;
use App\Domain\Video\VideoUploadSize;
use PHPUnit\Framework\TestCase;

final class VideoUploadSizeTest extends TestCase
{
    public function testAcceptsSizeWithinLimit(): void
    {
        VideoUploadSize::assertWithinLimit(512, 1024);

        self::assertTrue(true);
    }

    public function testRejectsEmptyUpload(): void
    {
        $this->expectException(InvalidVideoJobException::class);
        $this->expectExceptionMessage('Video upload must contain at least one byte.');

        VideoUploadSize::assertWithinLimit(0, 1024);
    }

    public function testRejectsUploadAboveLimit(): void
    {
        $this->expectException(InvalidVideoJobException::class);
        $this->expectExceptionMessage('Video upload exceeds the maximum allowed size of 1024 bytes.');

        VideoUploadSize::assertWithinLimit(2048, 1024);
    }
}
