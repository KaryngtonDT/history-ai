<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PipelineTelemetry',
    required: ['id', 'workspaceId', 'videoId', 'success', 'metrics', 'providerUsages', 'recordedAt'],
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'workspaceId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'videoId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'success', type: 'boolean'),
        new OA\Property(
            property: 'metrics',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/ExecutionMetric'),
        ),
        new OA\Property(
            property: 'providerUsages',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/ProviderUsage'),
        ),
        new OA\Property(property: 'recordedAt', type: 'string', format: 'date-time'),
        new OA\Property(property: 'batchJobId', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'qualityScore', type: 'integer', nullable: true),
        new OA\Property(property: 'errorMessage', type: 'string', nullable: true),
    ],
)]
final class PipelineTelemetrySchema
{
}
