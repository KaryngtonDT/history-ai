<?php

declare(strict_types=1);

namespace App\Application\Map\Queries;

final readonly class GetTimelineMapQuery
{
    public function __construct(
        public string $artifactId,
    ) {
    }
}
