<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'GraphNode',
    required: ['artifactId', 'type', 'title'],
    properties: [
        new OA\Property(
            property: 'artifactId',
            type: 'string',
            format: 'uuid',
            example: '550e8400-e29b-41d4-a716-446655440002',
        ),
        new OA\Property(
            property: 'type',
            ref: '#/components/schemas/ArtifactType',
        ),
        new OA\Property(
            property: 'title',
            type: 'string',
            example: 'Summary',
        ),
    ],
)]
final class GraphNode
{
}
