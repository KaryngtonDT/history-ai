<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ReviewScore',
    type: 'object',
    required: ['overall', 'translation', 'voice_clone', 'lip_sync', 'rendering'],
    properties: [
        new OA\Property(property: 'overall', type: 'integer', minimum: 1, maximum: 5),
        new OA\Property(property: 'translation', type: 'integer', minimum: 1, maximum: 5),
        new OA\Property(property: 'voice_clone', type: 'integer', minimum: 1, maximum: 5),
        new OA\Property(property: 'lip_sync', type: 'integer', minimum: 1, maximum: 5),
        new OA\Property(property: 'rendering', type: 'integer', minimum: 1, maximum: 5),
    ],
)]
final class ReviewScoreSchema
{
}
