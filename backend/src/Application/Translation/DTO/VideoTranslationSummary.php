<?php

declare(strict_types=1);

namespace App\Application\Translation\DTO;

final readonly class VideoTranslationSummary
{
    public function __construct(
        public string $videoId,
        public string $translationId,
        public string $sourceLanguage,
        public string $targetLanguage,
        public string $provider,
        public string $text,
        public int $segmentCount,
    ) {
    }
}
