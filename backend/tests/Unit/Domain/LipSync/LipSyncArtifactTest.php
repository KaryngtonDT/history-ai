<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\LipSync;

use App\Domain\LipSync\Exception\InvalidLipSyncException;
use App\Domain\LipSync\LipSyncArtifact;
use App\Domain\LipSync\LipSyncArtifactId;
use App\Domain\LipSync\LipSyncProvider;
use App\Domain\LipSync\LipSyncVideo;
use App\Domain\LipSync\LipSyncVideoId;
use App\Domain\TTS\AudioId;
use App\Domain\Video\VideoId;
use PHPUnit\Framework\TestCase;

final class LipSyncArtifactTest extends TestCase
{
    public function testCreateExposesFields(): void
    {
        $video = LipSyncVideo::create(
            new LipSyncVideoId('550e8400-e29b-41d4-a716-446655440070'),
            '/tmp/synced.mp4',
            120.5,
        );

        $artifact = LipSyncArtifact::create(
            new LipSyncArtifactId('550e8400-e29b-41d4-a716-446655440080'),
            new VideoId('550e8400-e29b-41d4-a716-446655440099'),
            new AudioId('550e8400-e29b-41d4-a716-446655440060'),
            LipSyncProvider::LatentSync,
            $video,
        );

        self::assertTrue($artifact->artifactId()->equals(
            new LipSyncArtifactId('550e8400-e29b-41d4-a716-446655440080'),
        ));
        self::assertTrue($artifact->sourceVideoId()->equals(
            new VideoId('550e8400-e29b-41d4-a716-446655440099'),
        ));
        self::assertSame(LipSyncProvider::LatentSync, $artifact->provider());
        self::assertSame($video, $artifact->video());
        self::assertTrue($artifact->audio()->equals(
            new AudioId('550e8400-e29b-41d4-a716-446655440060'),
        ));
    }
}
