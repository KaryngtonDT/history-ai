<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PerformanceMetricSnapshot',
    required: ['correlationId', 'recordedAt', 'metrics'],
    properties: [
        new OA\Property(
            property: 'correlationId',
            type: 'string',
            format: 'uuid',
            example: 'c6f98b8a-3f2e-4a1b-9c8d-1e2f3a4b5c6d',
        ),
        new OA\Property(
            property: 'recordedAt',
            type: 'string',
            format: 'date-time',
            example: '2026-06-28T20:00:00+00:00',
        ),
        new OA\Property(
            property: 'metrics',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/PerformanceMetric'),
        ),
    ],
)]
final class PerformanceMetricSnapshot
{
}
