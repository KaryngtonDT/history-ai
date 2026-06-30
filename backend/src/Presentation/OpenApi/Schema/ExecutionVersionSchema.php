<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ExecutionVersion',
    required: [
        'versionNumber',
        'pipelineConfigurationId',
        'optimizationId',
        'qualityReportId',
        'renderedVideoId',
        'createdAt',
        'optimizationProfile',
        'qualityScore',
    ],
    properties: [
        new OA\Property(property: 'versionNumber', type: 'integer', minimum: 1),
        new OA\Property(property: 'pipelineConfigurationId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'optimizationId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'qualityReportId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'renderedVideoId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
        new OA\Property(property: 'optimizationProfile', type: 'string', example: 'quality'),
        new OA\Property(property: 'qualityScore', type: 'integer', minimum: 0, maximum: 100),
    ],
)]
final class ExecutionVersionSchema
{
}
