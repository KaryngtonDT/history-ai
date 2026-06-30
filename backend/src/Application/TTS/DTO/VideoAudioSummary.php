<?php

declare(strict_types=1);

namespace App\Application\TTS\DTO;

final readonly class VideoAudioSummary
{
    public function __construct(
        public string $videoId,
        public string $audioId,
        public string $translationId,
        public string $targetLanguage,
        public string $provider,
        public string $voiceId,
        public string $voiceDisplayName,
        public float $duration,
        public string $format,
    ) {
    }
}
