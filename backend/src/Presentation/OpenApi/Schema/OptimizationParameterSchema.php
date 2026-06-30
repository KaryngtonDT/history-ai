<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'OptimizationParameter',
    required: ['key', 'value'],
    properties: [
        new OA\Property(property: 'key', type: 'string', example: 'beamSize'),
        new OA\Property(property: 'value', type: 'string', example: '5'),
    ],
)]
final class OptimizationParameterSchema
{
}
