<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\LipSync;

use App\Domain\LipSync\LipSyncArtifactId;
use App\Domain\LipSync\LipSyncProvider;
use App\Domain\LipSync\LipSyncVideoId;
use App\Domain\TTS\AudioId;
use App\Domain\Video\VideoId;
use App\Infrastructure\LipSync\Exception\LatentSyncProviderException;
use App\Infrastructure\LipSync\LipSyncMapper;
use PHPUnit\Framework\TestCase;

final class LipSyncMapperTest extends TestCase
{
    public function testToArtifactMapsProcessOutput(): void
    {
        $mapper = new LipSyncMapper();

        $artifact = $mapper->toArtifact(
            json_encode(['duration' => 12.5], JSON_THROW_ON_ERROR),
            new VideoId('550e8400-e29b-41d4-a716-446655440099'),
            new AudioId('550e8400-e29b-41d4-a716-446655440060'),
            LipSyncProvider::LatentSync,
            new LipSyncArtifactId('550e8400-e29b-41d4-a716-446655440080'),
            new LipSyncVideoId('550e8400-e29b-41d4-a716-446655440070'),
            '/tmp/synced.mp4',
        );

        self::assertSame(LipSyncProvider::LatentSync, $artifact->provider());
        self::assertSame(12.5, $artifact->video()->duration());
        self::assertSame('/tmp/synced.mp4', $artifact->video()->storagePath());
    }

    public function testInvalidJsonThrows(): void
    {
        $mapper = new LipSyncMapper();

        $this->expectException(LatentSyncProviderException::class);

        $mapper->toArtifact(
            'not-json',
            new VideoId('550e8400-e29b-41d4-a716-446655440099'),
            new AudioId('550e8400-e29b-41d4-a716-446655440060'),
            LipSyncProvider::LatentSync,
            new LipSyncArtifactId('550e8400-e29b-41d4-a716-446655440080'),
            new LipSyncVideoId('550e8400-e29b-41d4-a716-446655440070'),
            '/tmp/synced.mp4',
        );
    }
}
