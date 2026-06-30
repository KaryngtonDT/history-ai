<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AudioCharacteristics',
    required: ['language', 'speakerCount', 'backgroundNoise', 'backgroundMusic', 'speechSpeed', 'confidence'],
    properties: [
        new OA\Property(property: 'language', type: 'string', example: 'english'),
        new OA\Property(property: 'speakerCount', type: 'integer', example: 2),
        new OA\Property(property: 'backgroundNoise', type: 'string', example: 'low'),
        new OA\Property(property: 'backgroundMusic', type: 'string', example: 'detected'),
        new OA\Property(property: 'speechSpeed', type: 'string', example: 'fast'),
        new OA\Property(property: 'confidence', type: 'integer', minimum: 0, maximum: 100, example: 97),
    ],
)]
final class AudioCharacteristicsSchema
{
}
