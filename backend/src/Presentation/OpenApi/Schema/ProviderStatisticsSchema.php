<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProviderStatistics',
    required: ['providers'],
    properties: [
        new OA\Property(
            property: 'providers',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/ProviderStat'),
        ),
    ],
)]
final class ProviderStatisticsSchema
{
}
