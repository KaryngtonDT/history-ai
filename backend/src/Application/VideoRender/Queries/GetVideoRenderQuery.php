<?php

declare(strict_types=1);

namespace App\Application\VideoRender\Queries;

final readonly class GetVideoRenderQuery
{
    public function __construct(
        public string $videoId,
        public string $language,
    ) {
    }
}
