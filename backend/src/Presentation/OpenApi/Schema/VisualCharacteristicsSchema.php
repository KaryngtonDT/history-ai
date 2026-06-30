<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'VisualCharacteristics',
    required: ['resolution', 'fps', 'lighting', 'lipVisibility', 'faceCount'],
    properties: [
        new OA\Property(property: 'resolution', type: 'string', example: '1920x1080'),
        new OA\Property(property: 'fps', type: 'number', format: 'float', example: 30.0),
        new OA\Property(property: 'lighting', type: 'string', example: 'good'),
        new OA\Property(property: 'lipVisibility', type: 'string', example: 'excellent'),
        new OA\Property(property: 'faceCount', type: 'integer', example: 2),
    ],
)]
final class VisualCharacteristicsSchema
{
}
