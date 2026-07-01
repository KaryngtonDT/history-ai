<?php

declare(strict_types=1);

namespace App\Application\YouTube\DTO;

use App\Domain\YouTube\YouTubeMetadata;

final readonly class PreviewYouTubeResult
{
    public function __construct(
        public string $url,
        public YouTubeMetadata $metadata,
    ) {
    }
}
