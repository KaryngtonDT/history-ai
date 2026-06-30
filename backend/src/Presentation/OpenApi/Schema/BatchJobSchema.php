<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'BatchJob',
    required: [
        'id',
        'projectId',
        'status',
        'progress',
        'totalVideos',
        'queuedVideos',
        'targetLanguages',
        'failedVideoIds',
    ],
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'projectId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'status', ref: '#/components/schemas/BatchJobStatus'),
        new OA\Property(property: 'progress', type: 'integer', minimum: 0, maximum: 100),
        new OA\Property(property: 'totalVideos', type: 'integer', minimum: 0),
        new OA\Property(property: 'queuedVideos', type: 'integer', minimum: 0),
        new OA\Property(
            property: 'targetLanguages',
            type: 'array',
            items: new OA\Items(type: 'string'),
            example: ['fr', 'de'],
        ),
        new OA\Property(
            property: 'failedVideoIds',
            type: 'array',
            items: new OA\Items(type: 'string', format: 'uuid'),
        ),
    ],
)]
final class BatchJobSchema
{
}
