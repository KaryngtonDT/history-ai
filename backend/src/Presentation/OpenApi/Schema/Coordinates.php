<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Coordinates',
    required: ['latitude', 'longitude'],
    properties: [
        new OA\Property(
            property: 'latitude',
            type: 'number',
            format: 'float',
            example: 41.9028,
        ),
        new OA\Property(
            property: 'longitude',
            type: 'number',
            format: 'float',
            example: 12.4964,
        ),
    ],
)]
final class Coordinates
{
}
