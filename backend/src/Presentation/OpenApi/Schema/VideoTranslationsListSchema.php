<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'VideoTranslationsList',
    required: ['videoId', 'translations'],
    properties: [
        new OA\Property(
            property: 'videoId',
            type: 'string',
            format: 'uuid',
            example: '550e8400-e29b-41d4-a716-446655440099',
        ),
        new OA\Property(
            property: 'translations',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/VideoTranslationSummary'),
        ),
    ],
)]
final class VideoTranslationsListSchema
{
}
