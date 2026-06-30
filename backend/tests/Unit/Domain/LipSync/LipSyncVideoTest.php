<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\LipSync;

use App\Domain\LipSync\Exception\InvalidLipSyncException;
use App\Domain\LipSync\LipSyncVideo;
use App\Domain\LipSync\LipSyncVideoId;
use PHPUnit\Framework\TestCase;

final class LipSyncVideoTest extends TestCase
{
    public function testCreateExposesFields(): void
    {
        $video = LipSyncVideo::create(
            new LipSyncVideoId('550e8400-e29b-41d4-a716-446655440070'),
            '/tmp/synced.mp4',
            90.0,
        );

        self::assertTrue($video->synchronizedVideoId()->equals(
            new LipSyncVideoId('550e8400-e29b-41d4-a716-446655440070'),
        ));
        self::assertSame('/tmp/synced.mp4', $video->storagePath());
        self::assertSame(90.0, $video->duration());
    }

    public function testEmptyStoragePathThrows(): void
    {
        $this->expectException(InvalidLipSyncException::class);

        LipSyncVideo::create(
            new LipSyncVideoId('550e8400-e29b-41d4-a716-446655440070'),
            '   ',
            1.0,
        );
    }
}
