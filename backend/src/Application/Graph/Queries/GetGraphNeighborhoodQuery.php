<?php

declare(strict_types=1);

namespace App\Application\Graph\Queries;

final readonly class GetGraphNeighborhoodQuery
{
    public function __construct(
        public string $contentId,
        public string $artifactId,
    ) {
    }
}
