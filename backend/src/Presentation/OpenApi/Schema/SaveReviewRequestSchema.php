<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'SaveReviewRequest',
    required: ['scores'],
    properties: [
        new OA\Property(property: 'executionVersionNumber', type: 'integer', minimum: 1),
        new OA\Property(property: 'scores', ref: '#/components/schemas/ReviewScore'),
        new OA\Property(property: 'comment', ref: '#/components/schemas/ReviewComment'),
    ],
)]
final class SaveReviewRequestSchema
{
}
