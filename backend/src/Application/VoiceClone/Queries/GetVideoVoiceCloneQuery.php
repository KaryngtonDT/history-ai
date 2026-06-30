<?php

declare(strict_types=1);

namespace App\Application\VoiceClone\Queries;

final readonly class GetVideoVoiceCloneQuery
{
    public function __construct(
        public string $videoId,
        public string $language,
    ) {
    }
}
