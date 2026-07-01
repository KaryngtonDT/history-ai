<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'WorkspaceAnalytics',
    required: [
        'processedVideos',
        'averageProcessingTimeSeconds',
        'averageProcessingTimeLabel',
        'averageQuality',
        'successRate',
        'gpuUsagePercent',
        'topTranslationProvider',
        'topTtsProvider',
        'recentErrors',
    ],
    properties: [
        new OA\Property(property: 'processedVideos', type: 'integer'),
        new OA\Property(property: 'averageProcessingTimeSeconds', type: 'number', format: 'float'),
        new OA\Property(property: 'averageProcessingTimeLabel', type: 'string'),
        new OA\Property(property: 'averageQuality', type: 'integer'),
        new OA\Property(property: 'successRate', type: 'number', format: 'float'),
        new OA\Property(property: 'gpuUsagePercent', type: 'number', format: 'float'),
        new OA\Property(property: 'topTranslationProvider', type: 'string'),
        new OA\Property(property: 'topTtsProvider', type: 'string'),
        new OA\Property(
            property: 'recentErrors',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/RecentTelemetryError'),
        ),
    ],
)]
final class WorkspaceAnalyticsSchema
{
}
