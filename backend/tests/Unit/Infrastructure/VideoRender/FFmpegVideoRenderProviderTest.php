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
use App\Domain\VideoRender\VideoRenderFormat;
use App\Domain\VideoRender\VideoRenderProvider;
use App\Domain\VideoRender\VideoRenderQuality;
use App\Domain\LipSync\Exception\InvalidLipSyncException;
use App\Infrastructure\VideoRender\FFmpegVideoRenderProvider;
use App\Infrastructure\VideoRender\FixedFFmpegProcessRunner;
use App\Infrastructure\VideoRender\VideoRenderMapper;
use PHPUnit\Framework\TestCase;

final class FFmpegVideoRenderProviderTest extends TestCase
{
    private string $outputDirectory;

    protected function setUp(): void
    {
        $this->outputDirectory = sys_get_temp_dir().'/history-ai-render-'.uniqid('', true);
        mkdir($this->outputDirectory);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->outputDirectory)) {
            foreach (glob($this->outputDirectory.'/*') ?: [] as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($this->outputDirectory);
        }
    }

    public function testRenderReturnsFinalVideoArtifact(): void
    {
        $provider = new FFmpegVideoRenderProvider(
            new FixedFFmpegProcessRunner(),
            new VideoRenderMapper(),
            'ffmpeg',
            $this->outputDirectory,
        );

        $lipSync = $this->createLipSyncArtifact();
        $artifact = $provider->render($lipSync, VideoRenderFormat::MP4, VideoRenderQuality::Standard);

        self::assertSame(VideoRenderProvider::FFmpeg, $artifact->provider());
        self::assertSame(VideoRenderFormat::MP4, $artifact->format());
        self::assertSame(VideoRenderQuality::Standard, $artifact->quality());
        self::assertGreaterThan(0, $artifact->duration());
        self::assertGreaterThan(0, $artifact->fileSizeBytes());
    }

    public function testEmptyInputPathThrows(): void
    {
        $this->expectException(InvalidLipSyncException::class);

        LipSyncArtifact::create(
            new LipSyncArtifactId('550e8400-e29b-41d4-a716-446655440080'),
            new VideoId('550e8400-e29b-41d4-a716-446655440099'),
            new AudioId('550e8400-e29b-41d4-a716-446655440060'),
            LipSyncProvider::LatentSync,
            LipSyncVideo::create(
                new LipSyncVideoId('550e8400-e29b-41d4-a716-446655440070'),
                '   ',
                3.0,
            ),
        );
    }

    private function createLipSyncArtifact(): LipSyncArtifact
    {
        return LipSyncArtifact::create(
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
    }
}
