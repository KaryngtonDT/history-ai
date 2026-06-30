<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Review',
    required: ['id', 'videoId', 'executionVersionNumber', 'scores', 'comment', 'createdAt'],
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'videoId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'executionVersionNumber', type: 'integer', minimum: 1),
        new OA\Property(property: 'scores', ref: '#/components/schemas/ReviewScore'),
        new OA\Property(property: 'comment', ref: '#/components/schemas/ReviewComment'),
        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
    ],
)]
final class ReviewSchema
{
}
