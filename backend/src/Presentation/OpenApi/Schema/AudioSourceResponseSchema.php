<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AudioSourceResponse',
    required: ['audioId', 'title', 'originalFilename', 'status', 'type', 'createdAt'],
    properties: [
        new OA\Property(property: 'audioId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'title', type: 'string', example: 'podcast'),
        new OA\Property(property: 'originalFilename', type: 'string', example: 'podcast.mp3'),
        new OA\Property(property: 'status', ref: '#/components/schemas/SourceStatus'),
        new OA\Property(property: 'type', type: 'string', example: 'audio'),
        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
    ],
)]
final class AudioSourceResponseSchema
{
}
