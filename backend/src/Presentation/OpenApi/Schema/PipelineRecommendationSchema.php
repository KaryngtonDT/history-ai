<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PipelineRecommendation',
    required: [
        'id',
        'strategy',
        'explanation',
        'estimatedDurationSeconds',
        'estimatedQuality',
        'estimatedVramGb',
        'stages',
    ],
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'strategy', ref: '#/components/schemas/ProcessingStrategy'),
        new OA\Property(property: 'explanation', type: 'string'),
        new OA\Property(property: 'estimatedDurationSeconds', type: 'integer', example: 240),
        new OA\Property(property: 'estimatedQuality', type: 'integer', minimum: 1, maximum: 5, example: 4),
        new OA\Property(property: 'estimatedVramGb', type: 'number', format: 'float', example: 8.0),
        new OA\Property(
            property: 'stages',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/PipelineStage'),
        ),
        new OA\Property(
            property: 'reasons',
            type: 'array',
            items: new OA\Items(type: 'string'),
            example: ['Two speakers detected.', 'High STT confidence.'],
        ),
    ],
)]
final class PipelineRecommendationSchema
{
}
