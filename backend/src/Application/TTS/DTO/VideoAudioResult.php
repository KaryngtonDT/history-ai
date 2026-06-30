<?php

declare(strict_types=1);

namespace App\Application\TTS\DTO;

final readonly class VideoAudioResult
{
    public function __construct(
        public string $videoId,
        public string $audioId,
        public string $translationId,
        public string $targetLanguage,
        public string $provider,
        public string $voiceId,
        public string $voiceDisplayName,
        public string $voiceLanguage,
        public string $voiceGender,
        public float $duration,
        public string $format,
        public string $downloadUrl,
    ) {
    }
}
