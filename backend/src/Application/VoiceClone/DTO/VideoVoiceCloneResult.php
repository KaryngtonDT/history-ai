<?php

declare(strict_types=1);

namespace App\Application\VoiceClone\DTO;

final readonly class VideoVoiceCloneResult
{
    public function __construct(
        public string $videoId,
        public string $artifactId,
        public string $sourceAudioId,
        public string $clonedAudioId,
        public string $targetLanguage,
        public string $provider,
        public string $sourceLanguage,
        public float $duration,
        public int $sampleRate,
        public string $originalStreamPath,
    ) {
    }
}
