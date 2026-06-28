<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'RecommendedArtifact',
    required: ['artifactId', 'type', 'title', 'reason', 'score'],
    properties: [
        new OA\Property(
            property: 'artifactId',
            type: 'string',
            format: 'uuid',
            example: '550e8400-e29b-41d4-a716-446655440003',
        ),
        new OA\Property(
            property: 'type',
            ref: '#/components/schemas/ArtifactType',
        ),
        new OA\Property(
            property: 'title',
            type: 'string',
            example: 'Roman Empire Quiz',
        ),
        new OA\Property(
            property: 'reason',
            ref: '#/components/schemas/RecommendationReason',
        ),
        new OA\Property(
            property: 'score',
            type: 'integer',
            minimum: 0,
            maximum: 100,
            example: 80,
        ),
    ],
)]
final class RecommendedArtifact
{
}
