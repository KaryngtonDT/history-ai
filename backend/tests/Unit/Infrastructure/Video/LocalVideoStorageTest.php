<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Video;

use App\Domain\Video\VideoId;
use App\Infrastructure\Video\LocalVideoStorage;
use PHPUnit\Framework\TestCase;

final class LocalVideoStorageTest extends TestCase
{
    public function testStoresUploadedFileUsingVideoIdAndExtension(): void
    {
        $storageDirectory = sys_get_temp_dir() . '/video-storage-test-' . uniqid('', true);
        $sourcePath = tempnam(sys_get_temp_dir(), 'video-source-');
        self::assertNotFalse($sourcePath);
        file_put_contents($sourcePath, 'video-bytes');

        $videoId = VideoId::generate();
        $storage = new LocalVideoStorage($storageDirectory);
        $storedPath = null;

        try {
            $storedPath = $storage->store($videoId, $sourcePath, 'lecture.mp4');

            self::assertSame(
                sprintf('%s/%s.mp4', $storageDirectory, $videoId->value),
                $storedPath,
            );
            self::assertFileExists($storedPath);
            self::assertFalse(is_file($sourcePath));
        } finally {
            if (is_file($storedPath ?? '')) {
                @unlink($storedPath);
            }

            @rmdir($storageDirectory);
        }
    }
}
