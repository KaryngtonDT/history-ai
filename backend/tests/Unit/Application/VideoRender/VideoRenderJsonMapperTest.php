<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\VideoRender;

use App\Application\VideoRender\GenerateFinalVideoConfiguration;
use App\Application\VideoRender\VideoRenderJsonMapper;
use App\Domain\Translation\TranslationLanguage;
use App\Domain\VideoRender\FinalVideoArtifact;
use App\Domain\VideoRender\FinalVideoId;
use App\Domain\VideoRender\VideoRenderFormat;
use App\Domain\VideoRender\VideoRenderProvider;
use App\Domain\VideoRender\VideoRenderQuality;
use App\Domain\LipSync\LipSyncArtifactId;
use App\Domain\Video\VideoId;
use PHPUnit\Framework\TestCase;

final class VideoRenderJsonMapperTest extends TestCase
{
    private VideoRenderJsonMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new VideoRenderJsonMapper();
    }

    public function testRoundTripsArtifactWithStoragePath(): void
    {
        $artifact = FinalVideoArtifact::create(
            new FinalVideoId('550e8400-e29b-41d4-a716-446655440090'),
            new VideoId('550e8400-e29b-41d4-a716-446655440099'),
            new LipSyncArtifactId('550e8400-e29b-41d4-a716-446655440080'),
            VideoRenderProvider::FFmpeg,
            VideoRenderFormat::MP4,
            VideoRenderQuality::Standard,
            3.5,
            4096,
        );

        $json = $this->mapper->toJson($artifact, TranslationLanguage::French, '/tmp/final.mp4');
        $decoded = $this->mapper->fromJson($json);

        self::assertTrue($artifact->finalVideoId()->equals($decoded->finalVideoId()));
        self::assertSame('/tmp/final.mp4', $this->mapper->storagePathFromJson($json));
        self::assertSame(TranslationLanguage::French, $this->mapper->targetLanguageFromJson($json));
    }
}
