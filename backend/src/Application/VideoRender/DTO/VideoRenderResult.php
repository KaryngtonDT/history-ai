<?php

declare(strict_types=1);

namespace App\Application\VideoRender\DTO;

final readonly class VideoRenderResult
{
    public function __construct(
        public string $videoId,
        public string $finalVideoId,
        public string $targetLanguage,
        public string $provider,
        public string $format,
        public string $quality,
        public float $duration,
        public int $fileSizeBytes,
    ) {
    }
}
