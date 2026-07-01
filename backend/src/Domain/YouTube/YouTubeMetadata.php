<?php

declare(strict_types=1);

namespace App\Domain\YouTube;

final readonly class YouTubeMetadata
{
    public function __construct(
        public string $title,
        public ?int $durationSeconds,
        public ?string $thumbnailUrl,
        public ?string $language,
        public ?string $channelName = null,
    ) {
    }
}
