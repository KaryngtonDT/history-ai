<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'VideoAnalysis',
    properties: [
        new OA\Property(property: 'detectedLanguage', type: 'string', example: 'english'),
        new OA\Property(property: 'durationSeconds', type: 'number', format: 'float', example: 120.0),
        new OA\Property(property: 'resolution', type: 'string', example: '1920x1080'),
        new OA\Property(property: 'fps', type: 'number', format: 'float', example: 30.0),
        new OA\Property(property: 'gpuAvailable', type: 'boolean', example: true),
        new OA\Property(property: 'estimatedVramGb', type: 'number', format: 'float', example: 8.0),
        new OA\Property(property: 'segmentCount', type: 'integer', example: 25),
        new OA\Property(property: 'transcriptText', type: 'string'),
        new OA\Property(property: 'hasSlidesHint', type: 'boolean', example: false),
        new OA\Property(property: 'strategy', ref: '#/components/schemas/ProcessingStrategy'),
    ],
)]
final class VideoAnalysisSchema
{
}
