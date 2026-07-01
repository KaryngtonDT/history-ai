<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ExecutionMetric',
    required: ['type', 'value', 'unit'],
    properties: [
        new OA\Property(property: 'type', type: 'string'),
        new OA\Property(property: 'value', type: 'number', format: 'float'),
        new OA\Property(property: 'unit', type: 'string'),
    ],
)]
final class ExecutionMetricSchema
{
}
