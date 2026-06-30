<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ExecutionSnapshot',
    required: [
        'versionNumber',
        'pipelineConfigurationId',
        'optimizationId',
        'qualityReportId',
        'renderedVideoId',
        'createdAt',
        'pipelineConfiguration',
        'optimization',
        'qualityReport',
    ],
    properties: [
        new OA\Property(property: 'versionNumber', type: 'integer'),
        new OA\Property(property: 'pipelineConfigurationId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'optimizationId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'qualityReportId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'renderedVideoId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
        new OA\Property(property: 'pipelineConfiguration', type: 'object'),
        new OA\Property(property: 'optimization', type: 'object'),
        new OA\Property(property: 'qualityReport', type: 'object'),
    ],
)]
final class ExecutionSnapshotSchema
{
}
