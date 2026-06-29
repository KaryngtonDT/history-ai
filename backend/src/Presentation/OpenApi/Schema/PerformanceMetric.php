<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PerformanceMetric',
    required: ['name', 'durationMs'],
    properties: [
        new OA\Property(
            property: 'name',
            type: 'string',
            example: 'chunking_ms',
        ),
        new OA\Property(
            property: 'durationMs',
            type: 'integer',
            minimum: 0,
            example: 12,
        ),
    ],
)]
final class PerformanceMetric
{
}
