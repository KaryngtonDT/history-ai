<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\VideoRender;

use App\Domain\LipSync\LipSyncArtifact;
use App\Domain\LipSync\LipSyncArtifactId;
use App\Domain\LipSync\LipSyncProvider;
use App\Domain\LipSync\LipSyncVideo;
use App\Domain\LipSync\LipSyncVideoId;
use App\Domain\TTS\AudioId;
use App\Domain\Video\VideoId;
use App\Domain\VideoRender\FinalVideoArtifact;
use App\Domain\VideoRender\FinalVideoId;
use App\Domain\VideoRender\VideoRenderFormat;
use App\Domain\VideoRender\VideoRenderProvider;
use App\Domain\VideoRender\VideoRenderProviderInterface;
use App\Domain\VideoRender\VideoRenderQuality;
use PHPUnit\Framework\TestCase;

final class VideoRenderProviderInterfaceTest extends TestCase
{
    public function testProviderInterfaceDefinesRenderMethod(): void
    {
        $lipSync = LipSyncArtifact::create(
            new LipSyncArtifactId('550e8400-e29b-41d4-a716-446655440080'),
            new VideoId('550e8400-e29b-41d4-a716-446655440099'),
            new AudioId('550e8400-e29b-41d4-a716-446655440060'),
            LipSyncProvider::LatentSync,
            LipSyncVideo::create(
                new LipSyncVideoId('550e8400-e29b-41d4-a716-446655440070'),
                '/tmp/synced.mp4',
                90.0,
            ),
        );

        $expected = FinalVideoArtifact::create(
            new FinalVideoId('550e8400-e29b-41d4-a716-446655440090'),
            $lipSync->sourceVideoId(),
            $lipSync->artifactId(),
            VideoRenderProvider::FFmpeg,
            VideoRenderFormat::MP4,
            VideoRenderQuality::Standard,
            90.0,
            12_000_000,
        );

        $provider = $this->createMock(VideoRenderProviderInterface::class);
        $provider
            ->expects(self::once())
            ->method('render')
            ->with($lipSync, VideoRenderFormat::MP4, VideoRenderQuality::Standard)
            ->willReturn($expected);

        self::assertSame(
            $expected,
            $provider->render($lipSync, VideoRenderFormat::MP4, VideoRenderQuality::Standard),
        );
    }
}
