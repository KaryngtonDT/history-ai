<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ExecutionHistory',
    required: ['id', 'videoId', 'versions'],
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'videoId', type: 'string', format: 'uuid'),
        new OA\Property(
            property: 'versions',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/ExecutionVersion'),
        ),
    ],
)]
final class ExecutionHistorySchema
{
}
