<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ComparisonResult',
    required: [
        'leftVersion',
        'rightVersion',
        'providerDifferences',
        'optimizationDifference',
        'qualityScoreDifference',
    ],
    properties: [
        new OA\Property(property: 'leftVersion', type: 'integer'),
        new OA\Property(property: 'rightVersion', type: 'integer'),
        new OA\Property(
            property: 'providerDifferences',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'stage', type: 'string'),
                    new OA\Property(property: 'leftProvider', type: 'string'),
                    new OA\Property(property: 'rightProvider', type: 'string'),
                ],
            ),
        ),
        new OA\Property(property: 'optimizationDifference', type: 'object', nullable: true),
        new OA\Property(property: 'qualityScoreDifference', type: 'object', nullable: true),
    ],
)]
final class ComparisonResultSchema
{
}
