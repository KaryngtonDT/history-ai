<?php

declare(strict_types=1);

namespace App\Application\Video\Queries;

final readonly class GetVideoStatusQuery
{
    public function __construct(
        public string $videoId,
    ) {
    }
}
