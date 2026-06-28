<?php

declare(strict_types=1);

namespace App\Application\Semantic\Queries;

final readonly class SearchSemanticChunksQuery
{
    public function __construct(
        public string $contentId,
        public string $query,
    ) {
    }
}
