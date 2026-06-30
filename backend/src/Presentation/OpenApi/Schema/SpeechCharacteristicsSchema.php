<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'SpeechCharacteristics',
    required: ['dominantEmotion', 'averageSpeakingRate', 'pauseCount', 'hasOverlaps'],
    properties: [
        new OA\Property(property: 'dominantEmotion', type: 'string', example: 'neutral'),
        new OA\Property(property: 'averageSpeakingRate', type: 'number', format: 'float', example: 160.0),
        new OA\Property(property: 'pauseCount', type: 'integer', example: 12),
        new OA\Property(property: 'hasOverlaps', type: 'boolean', example: false),
    ],
)]
final class SpeechCharacteristicsSchema
{
}
