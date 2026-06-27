<?php

declare(strict_types=1);

namespace App\Application\Timeline\Queries;

final readonly class GetTimelineQuery
{
    public function __construct(
        public string $artifactId,
    ) {
    }
}
