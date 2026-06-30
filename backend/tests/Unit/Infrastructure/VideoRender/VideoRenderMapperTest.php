<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\VideoRender;

use App\Domain\LipSync\LipSyncArtifact;
use App\Domain\LipSync\LipSyncArtifactId;
use App\Domain\LipSync\LipSyncProvider;
use App\Domain\LipSync\LipSyncVideo;
use App\Domain\LipSync\LipSyncVideoId;
use App\Domain\TTS\AudioId;
use App\Domain\Video\VideoId;
use App\Domain\VideoRender\FinalVideoId;
use App\Domain\VideoRender\VideoRenderFormat;
use App\Domain\VideoRender\VideoRenderProvider;
use App\Domain\VideoRender\VideoRenderQuality;
use App\Infrastructure\VideoRender\Exception\FFmpegProviderException;
use App\Infrastructure\VideoRender\VideoRenderMapper;
use PHPUnit\Framework\TestCase;

final class VideoRenderMapperTest extends TestCase
{
    public function testToArtifactMapsProcessOutput(): void
    {
        $mapper = new VideoRenderMapper();
        $lipSync = LipSyncArtifact::create(
            new LipSyncArtifactId('550e8400-e29b-41d4-a716-446655440080'),
            new VideoId('550e8400-e29b-41d4-a716-446655440099'),
            new AudioId('550e8400-e29b-41d4-a716-446655440060'),
            LipSyncProvider::LatentSync,
            LipSyncVideo::create(
                new LipSyncVideoId('550e8400-e29b-41d4-a716-446655440070'),
                '/tmp/synced.mp4',
                45.0,
            ),
        );

        $artifact = $mapper->toArtifact(
            json_encode(['duration' => 45.0, 'fileSizeBytes' => 8_500_000], JSON_THROW_ON_ERROR),
            $lipSync,
            VideoRenderProvider::FFmpeg,
            VideoRenderFormat::MP4,
            VideoRenderQuality::High,
            new FinalVideoId('550e8400-e29b-41d4-a716-446655440090'),
        );

        self::assertSame(VideoRenderProvider::FFmpeg, $artifact->provider());
        self::assertSame(45.0, $artifact->duration());
        self::assertSame(8_500_000, $artifact->fileSizeBytes());
    }

    public function testInvalidJsonThrows(): void
    {
        $mapper = new VideoRenderMapper();
        $lipSync = LipSyncArtifact::create(
            new LipSyncArtifactId('550e8400-e29b-41d4-a716-446655440080'),
            new VideoId('550e8400-e29b-41d4-a716-446655440099'),
            new AudioId('550e8400-e29b-41d4-a716-446655440060'),
            LipSyncProvider::LatentSync,
            LipSyncVideo::create(
                new LipSyncVideoId('550e8400-e29b-41d4-a716-446655440070'),
                '/tmp/synced.mp4',
                1.0,
            ),
        );

        $this->expectException(FFmpegProviderException::class);

        $mapper->toArtifact(
            'not-json',
            $lipSync,
            VideoRenderProvider::FFmpeg,
            VideoRenderFormat::MP4,
            VideoRenderQuality::Standard,
            new FinalVideoId('550e8400-e29b-41d4-a716-446655440090'),
        );
    }
}
