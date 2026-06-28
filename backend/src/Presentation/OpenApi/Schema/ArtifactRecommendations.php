<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ArtifactRecommendations',
    required: ['recommendations'],
    properties: [
        new OA\Property(
            property: 'recommendations',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/RecommendedArtifact'),
        ),
    ],
)]
final class ArtifactRecommendations
{
}
