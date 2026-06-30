<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ExecutionOptimization',
    required: [
        'id',
        'videoId',
        'profile',
        'summary',
        'estimatedImpact',
        'stages',
        'explanations',
    ],
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'videoId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'profile', ref: '#/components/schemas/OptimizationProfile'),
        new OA\Property(property: 'summary', type: 'string', example: 'Quality execution optimization for english content.'),
        new OA\Property(property: 'estimatedImpact', type: 'integer', minimum: 1, maximum: 5, example: 5),
        new OA\Property(
            property: 'stages',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/OptimizationStage'),
        ),
        new OA\Property(
            property: 'explanations',
            type: 'array',
            items: new OA\Items(type: 'string'),
        ),
    ],
)]
final class ExecutionOptimizationSchema
{
}
