<?php

declare(strict_types=1);

namespace App\Application\TTS\Queries;

final readonly class GetVideoAudioQuery
{
    public function __construct(
        public string $videoId,
        public string $language,
    ) {
    }
}
