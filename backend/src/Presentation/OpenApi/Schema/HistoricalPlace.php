<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'HistoricalPlace',
    required: ['name', 'coordinates'],
    properties: [
        new OA\Property(
            property: 'name',
            type: 'string',
            example: 'Rome',
        ),
        new OA\Property(
            property: 'coordinates',
            ref: '#/components/schemas/Coordinates',
        ),
        new OA\Property(
            property: 'description',
            type: 'string',
            nullable: true,
            example: '753 BC — Foundation of Rome',
        ),
    ],
)]
final class HistoricalPlace
{
}
