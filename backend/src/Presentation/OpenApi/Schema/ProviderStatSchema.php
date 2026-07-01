<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProviderStat',
    required: ['stage', 'providerId', 'invocationCount', 'averageDurationSeconds'],
    properties: [
        new OA\Property(property: 'stage', type: 'string'),
        new OA\Property(property: 'providerId', type: 'string'),
        new OA\Property(property: 'invocationCount', type: 'integer'),
        new OA\Property(property: 'averageDurationSeconds', type: 'number', format: 'float'),
    ],
)]
final class ProviderStatSchema
{
}
