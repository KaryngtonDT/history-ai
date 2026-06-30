<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'QualityMetric',
    required: ['category', 'score', 'explanation'],
    properties: [
        new OA\Property(property: 'category', ref: '#/components/schemas/QualityCategory'),
        new OA\Property(property: 'score', ref: '#/components/schemas/QualityScore'),
        new OA\Property(property: 'explanation', type: 'string', example: 'Clean audio track with low background noise.'),
    ],
)]
final class QualityMetricSchema
{
}
