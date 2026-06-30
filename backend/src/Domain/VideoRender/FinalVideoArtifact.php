<?php

declare(strict_types=1);

namespace App\Domain\VideoRender;

use App\Domain\LipSync\LipSyncArtifactId;
use App\Domain\Video\VideoId;
use App\Domain\VideoRender\Exception\InvalidVideoRenderException;

final readonly class FinalVideoArtifact
{
    public function __construct(
        private FinalVideoId $finalVideoId,
        private VideoId $videoId,
        private LipSyncArtifactId $lipSyncArtifactId,
        private VideoRenderProvider $provider,
        private VideoRenderFormat $format,
        private VideoRenderQuality $quality,
        private float $duration,
        private int $fileSizeBytes,
    ) {
        if ($this->duration < 0) {
            throw new InvalidVideoRenderException('Final video duration cannot be negative.');
        }

        if ($this->fileSizeBytes < 0) {
            throw new InvalidVideoRenderException('Final video file size cannot be negative.');
        }
    }

    public static function create(
        FinalVideoId $finalVideoId,
        VideoId $videoId,
        LipSyncArtifactId $lipSyncArtifactId,
        VideoRenderProvider $provider,
        VideoRenderFormat $format,
        VideoRenderQuality $quality,
        float $duration,
        int $fileSizeBytes,
    ): self {
        return new self(
            $finalVideoId,
            $videoId,
            $lipSyncArtifactId,
            $provider,
            $format,
            $quality,
            $duration,
            $fileSizeBytes,
        );
    }

    public function finalVideoId(): FinalVideoId
    {
        return $this->finalVideoId;
    }

    public function videoId(): VideoId
    {
        return $this->videoId;
    }

    public function lipSyncArtifactId(): LipSyncArtifactId
    {
        return $this->lipSyncArtifactId;
    }

    public function provider(): VideoRenderProvider
    {
        return $this->provider;
    }

    public function format(): VideoRenderFormat
    {
        return $this->format;
    }

    public function quality(): VideoRenderQuality
    {
        return $this->quality;
    }

    public function duration(): float
    {
        return $this->duration;
    }

    public function fileSizeBytes(): int
    {
        return $this->fileSizeBytes;
    }
}
