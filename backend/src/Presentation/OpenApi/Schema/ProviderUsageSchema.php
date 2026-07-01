<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProviderUsage',
    required: ['stage', 'providerId', 'invocationCount', 'totalDurationSeconds'],
    properties: [
        new OA\Property(property: 'stage', type: 'string'),
        new OA\Property(property: 'providerId', type: 'string'),
        new OA\Property(property: 'invocationCount', type: 'integer'),
        new OA\Property(property: 'totalDurationSeconds', type: 'number', format: 'float'),
    ],
)]
final class ProviderUsageSchema
{
}
