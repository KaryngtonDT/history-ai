<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\VideoRender;

use App\Domain\LipSync\LipSyncArtifactId;
use App\Domain\Video\VideoId;
use App\Domain\VideoRender\Exception\InvalidVideoRenderException;
use App\Domain\VideoRender\FinalVideoArtifact;
use App\Domain\VideoRender\FinalVideoId;
use App\Domain\VideoRender\VideoRenderFormat;
use App\Domain\VideoRender\VideoRenderProvider;
use App\Domain\VideoRender\VideoRenderQuality;
use PHPUnit\Framework\TestCase;

final class FinalVideoArtifactTest extends TestCase
{
    public function testCreateExposesFields(): void
    {
        $artifact = FinalVideoArtifact::create(
            new FinalVideoId('550e8400-e29b-41d4-a716-446655440090'),
            new VideoId('550e8400-e29b-41d4-a716-446655440099'),
            new LipSyncArtifactId('550e8400-e29b-41d4-a716-446655440080'),
            VideoRenderProvider::FFmpeg,
            VideoRenderFormat::MP4,
            VideoRenderQuality::Standard,
            120.5,
            15_000_000,
        );

        self::assertTrue($artifact->finalVideoId()->equals(
            new FinalVideoId('550e8400-e29b-41d4-a716-446655440090'),
        ));
        self::assertTrue($artifact->videoId()->equals(
            new VideoId('550e8400-e29b-41d4-a716-446655440099'),
        ));
        self::assertSame(VideoRenderProvider::FFmpeg, $artifact->provider());
        self::assertSame(VideoRenderFormat::MP4, $artifact->format());
        self::assertSame(VideoRenderQuality::Standard, $artifact->quality());
        self::assertSame(120.5, $artifact->duration());
        self::assertSame(15_000_000, $artifact->fileSizeBytes());
    }

    public function testNegativeDurationThrows(): void
    {
        $this->expectException(InvalidVideoRenderException::class);

        FinalVideoArtifact::create(
            new FinalVideoId('550e8400-e29b-41d4-a716-446655440090'),
            new VideoId('550e8400-e29b-41d4-a716-446655440099'),
            new LipSyncArtifactId('550e8400-e29b-41d4-a716-446655440080'),
            VideoRenderProvider::FFmpeg,
            VideoRenderFormat::MP4,
            VideoRenderQuality::Standard,
            -1.0,
            1000,
        );
    }
}
