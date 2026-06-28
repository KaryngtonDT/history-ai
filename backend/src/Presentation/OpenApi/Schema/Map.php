<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Map',
    required: ['places'],
    properties: [
        new OA\Property(
            property: 'places',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/HistoricalPlace'),
        ),
    ],
)]
final class Map
{
}
