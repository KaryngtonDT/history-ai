<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'QualityReport',
    required: [
        'id',
        'videoId',
        'overallScore',
        'recommendation',
        'metrics',
        'explanations',
    ],
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'videoId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'overallScore', ref: '#/components/schemas/QualityScore'),
        new OA\Property(property: 'recommendation', ref: '#/components/schemas/PublicationRecommendation'),
        new OA\Property(
            property: 'metrics',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/QualityMetric'),
        ),
        new OA\Property(
            property: 'explanations',
            type: 'array',
            items: new OA\Items(type: 'string'),
        ),
    ],
)]
final class QualityReportSchema
{
}
