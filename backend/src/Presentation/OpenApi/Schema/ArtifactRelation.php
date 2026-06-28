<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ArtifactRelation',
    required: ['sourceArtifactId', 'targetArtifactId', 'type'],
    properties: [
        new OA\Property(
            property: 'sourceArtifactId',
            type: 'string',
            format: 'uuid',
            example: '550e8400-e29b-41d4-a716-446655440002',
        ),
        new OA\Property(
            property: 'targetArtifactId',
            type: 'string',
            format: 'uuid',
            example: '550e8400-e29b-41d4-a716-446655440001',
        ),
        new OA\Property(
            property: 'type',
            ref: '#/components/schemas/ArtifactRelationType',
        ),
    ],
)]
final class ArtifactRelation
{
}
