<?php

declare(strict_types=1);

namespace App\Application\LipSync\DTO;

final readonly class VideoLipSyncSummary
{
    public function __construct(
        public string $videoId,
        public string $artifactId,
        public string $clonedAudioId,
        public string $targetLanguage,
        public string $provider,
        public string $synchronizedVideoId,
        public float $duration,
    ) {
    }
}
