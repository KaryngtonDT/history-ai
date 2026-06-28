<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'RetrievedChunk',
    required: ['artifactId', 'chunkId', 'position', 'text', 'score'],
    properties: [
        new OA\Property(
            property: 'artifactId',
            type: 'string',
            format: 'uuid',
            example: '550e8400-e29b-41d4-a716-446655440002',
        ),
        new OA\Property(
            property: 'chunkId',
            type: 'string',
            format: 'uuid',
            example: '550e8400-e29b-41d4-a716-446655440010',
        ),
        new OA\Property(
            property: 'position',
            type: 'integer',
            minimum: 0,
            example: 0,
        ),
        new OA\Property(
            property: 'text',
            type: 'string',
            example: '## Ancient Rome\n753 BC — Foundation of Rome',
        ),
        new OA\Property(
            property: 'score',
            type: 'number',
            format: 'float',
            minimum: 0,
            maximum: 1,
            example: 0.87,
        ),
    ],
)]
final class RetrievedChunk
{
}
