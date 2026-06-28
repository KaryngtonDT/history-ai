<?php

declare(strict_types=1);

namespace App\Application\Relation\Queries;

final readonly class GetArtifactRelationsQuery
{
    public function __construct(
        public string $contentId,
    ) {
    }
}
