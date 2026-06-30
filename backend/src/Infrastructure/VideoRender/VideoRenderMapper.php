<?php

declare(strict_types=1);

namespace App\Infrastructure\VideoRender;

use App\Domain\LipSync\LipSyncArtifact;
use App\Domain\VideoRender\FinalVideoArtifact;
use App\Domain\VideoRender\FinalVideoId;
use App\Domain\VideoRender\VideoRenderFormat;
use App\Domain\VideoRender\VideoRenderProvider;
use App\Domain\VideoRender\VideoRenderQuality;
use App\Infrastructure\VideoRender\Exception\FFmpegProviderException;

final class VideoRenderMapper
{
    public function toArtifact(
        string $processOutput,
        LipSyncArtifact $lipSync,
        VideoRenderProvider $provider,
        VideoRenderFormat $format,
        VideoRenderQuality $quality,
        FinalVideoId $finalVideoId,
    ): FinalVideoArtifact {
        /** @var array<string, mixed>|null $payload */
        $payload = json_decode($processOutput, true);

        if (!is_array($payload)) {
            throw new FFmpegProviderException('FFmpeg process output must be valid JSON.');
        }

        $duration = $payload['duration'] ?? null;
        $fileSizeBytes = $payload['fileSizeBytes'] ?? null;

        if (!is_numeric($duration)) {
            throw new FFmpegProviderException('FFmpeg process output must include duration.');
        }

        if (!is_numeric($fileSizeBytes) || (int) $fileSizeBytes < 0) {
            throw new FFmpegProviderException('FFmpeg process output must include file size.');
        }

        return FinalVideoArtifact::create(
            $finalVideoId,
            $lipSync->sourceVideoId(),
            $lipSync->artifactId(),
            $provider,
            $format,
            $quality,
            (float) $duration,
            (int) $fileSizeBytes,
        );
    }
}
