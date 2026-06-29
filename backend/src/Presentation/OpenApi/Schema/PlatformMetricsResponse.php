<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PlatformMetricsResponse',
    required: ['snapshots'],
    properties: [
        new OA\Property(
            property: 'snapshots',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/PerformanceMetricSnapshot'),
        ),
    ],
)]
final class PlatformMetricsResponse
{
}
