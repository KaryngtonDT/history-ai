<?php

declare(strict_types=1);

namespace App\Application\Artifact\Queries;

final readonly class ListArtifactsByContentQuery
{
    public function __construct(
        public string $contentId,
    ) {
    }
}
