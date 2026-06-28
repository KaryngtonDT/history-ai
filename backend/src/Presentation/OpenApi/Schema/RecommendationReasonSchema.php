<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'RecommendationReason',
    type: 'string',
    description: 'Semantic reason explaining why an artifact is recommended.',
    enum: ['related', 'derived_from', 'references', 'next', 'previous'],
    example: 'derived_from',
)]
final class RecommendationReasonSchema
{
}
