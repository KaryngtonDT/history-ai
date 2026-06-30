<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'VideoRenderSummary',
    required: [
        'videoId',
        'finalVideoId',
        'targetLanguage',
        'provider',
        'format',
        'quality',
        'duration',
        'fileSizeBytes',
        'streamUrl',
    ],
    properties: [
        new OA\Property(property: 'videoId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'finalVideoId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'targetLanguage', ref: '#/components/schemas/TranslationLanguage'),
        new OA\Property(property: 'provider', ref: '#/components/schemas/VideoRenderProvider'),
        new OA\Property(property: 'format', ref: '#/components/schemas/VideoRenderFormat'),
        new OA\Property(property: 'quality', ref: '#/components/schemas/VideoRenderQuality'),
        new OA\Property(property: 'duration', type: 'number', format: 'float', example: 120.5),
        new OA\Property(property: 'fileSizeBytes', type: 'integer', example: 1048576),
        new OA\Property(property: 'streamUrl', type: 'string', example: '/api/videos/{videoId}/render/french/stream'),
    ],
)]
final class VideoRenderSummarySchema
{
}
