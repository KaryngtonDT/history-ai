<?php

declare(strict_types=1);

namespace App\Application\LipSync\Queries;

final readonly class GetVideoLipSyncQuery
{
    public function __construct(
        public string $videoId,
        public string $language,
    ) {
    }
}
