<?php

declare(strict_types=1);

namespace App\Application\Translation\Queries;

final readonly class GetVideoTranslationQuery
{
    public function __construct(
        public string $videoId,
        public string $language,
    ) {
    }
}
