<?php

declare(strict_types=1);

namespace App\Application\Recommendation\Queries;

final readonly class GetArtifactRecommendationsQuery
{
    public function __construct(
        public string $contentId,
        public string $artifactId,
    ) {
    }
}
