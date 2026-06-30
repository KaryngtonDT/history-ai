<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\LipSync;

use App\Application\LipSync\LipSyncJsonMapper;
use App\Domain\LipSync\Exception\InvalidLipSyncException;
use App\Domain\LipSync\LipSyncArtifact;
use App\Domain\LipSync\LipSyncArtifactId;
use App\Domain\LipSync\LipSyncProvider;
use App\Domain\LipSync\LipSyncVideo;
use App\Domain\LipSync\LipSyncVideoId;
use App\Domain\Translation\TranslationLanguage;
use App\Domain\TTS\AudioId;
use App\Domain\Video\VideoId;
use PHPUnit\Framework\TestCase;

final class LipSyncJsonMapperTest extends TestCase
{
    public function testRoundTripPreservesArtifact(): void
    {
        $mapper = new LipSyncJsonMapper();
        $artifact = LipSyncArtifact::create(
            new LipSyncArtifactId('550e8400-e29b-41d4-a716-446655440080'),
            new VideoId('550e8400-e29b-41d4-a716-446655440099'),
            new AudioId('550e8400-e29b-41d4-a716-446655440060'),
            LipSyncProvider::LatentSync,
            LipSyncVideo::create(
                new LipSyncVideoId('550e8400-e29b-41d4-a716-446655440070'),
                '/tmp/synced.mp4',
                12.0,
            ),
        );

        $json = $mapper->toJson($artifact, TranslationLanguage::French);
        $restored = $mapper->fromJson($json);

        self::assertTrue($restored->artifactId()->equals($artifact->artifactId()));
        self::assertSame(LipSyncProvider::LatentSync, $restored->provider());
        self::assertSame(12.0, $restored->video()->duration());
        self::assertSame(TranslationLanguage::French, $mapper->targetLanguageFromJson($json));
    }

    public function testInvalidJsonThrows(): void
    {
        $mapper = new LipSyncJsonMapper();

        $this->expectException(InvalidLipSyncException::class);

        $mapper->fromJson('not-json');
    }
}
