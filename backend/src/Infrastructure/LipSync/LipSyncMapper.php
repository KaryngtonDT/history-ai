<?php

declare(strict_types=1);

namespace App\Infrastructure\LipSync;

use App\Domain\LipSync\LipSyncArtifact;
use App\Domain\LipSync\LipSyncArtifactId;
use App\Domain\LipSync\LipSyncProvider;
use App\Domain\LipSync\LipSyncVideo;
use App\Domain\LipSync\LipSyncVideoId;
use App\Domain\TTS\AudioId;
use App\Domain\Video\VideoId;
use App\Infrastructure\LipSync\Exception\LatentSyncProviderException;

final class LipSyncMapper
{
    public function toArtifact(
        string $processOutput,
        VideoId $sourceVideoId,
        AudioId $clonedAudioId,
        LipSyncProvider $provider,
        LipSyncArtifactId $artifactId,
        LipSyncVideoId $synchronizedVideoId,
        string $storagePath,
    ): LipSyncArtifact {
        /** @var array<string, mixed>|null $payload */
        $payload = json_decode($processOutput, true);

        if (!is_array($payload)) {
            throw new LatentSyncProviderException('LatentSync process output must be valid JSON.');
        }

        $duration = $payload['duration'] ?? null;

        if (!is_numeric($duration)) {
            throw new LatentSyncProviderException('LatentSync process output must include duration.');
        }

        $video = LipSyncVideo::create(
            $synchronizedVideoId,
            $storagePath,
            (float) $duration,
        );

        return LipSyncArtifact::create(
            $artifactId,
            $sourceVideoId,
            $clonedAudioId,
            $provider,
            $video,
        );
    }
}
