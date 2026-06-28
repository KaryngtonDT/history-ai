<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ChatCitation',
    required: ['number', 'artifactId', 'chunkId', 'score'],
    properties: [
        new OA\Property(
            property: 'number',
            type: 'integer',
            minimum: 1,
            example: 1,
        ),
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
            property: 'score',
            type: 'number',
            format: 'float',
            minimum: 0,
            maximum: 1,
            example: 0.87,
        ),
    ],
)]
final class ChatCitation
{
}
