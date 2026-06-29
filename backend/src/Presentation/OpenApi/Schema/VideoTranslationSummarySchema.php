<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'VideoTranslationSummary',
    required: [
        'videoId',
        'translationId',
        'sourceLanguage',
        'targetLanguage',
        'provider',
        'text',
        'segmentCount',
    ],
    properties: [
        new OA\Property(
            property: 'videoId',
            type: 'string',
            format: 'uuid',
            example: '550e8400-e29b-41d4-a716-446655440099',
        ),
        new OA\Property(
            property: 'translationId',
            type: 'string',
            format: 'uuid',
            example: '550e8400-e29b-41d4-a716-446655440020',
        ),
        new OA\Property(
            property: 'sourceLanguage',
            ref: '#/components/schemas/TranslationLanguage',
        ),
        new OA\Property(
            property: 'targetLanguage',
            ref: '#/components/schemas/TranslationLanguage',
        ),
        new OA\Property(
            property: 'provider',
            ref: '#/components/schemas/TranslationProvider',
        ),
        new OA\Property(property: 'text', type: 'string', example: 'Bonjour tout le monde'),
        new OA\Property(property: 'segmentCount', type: 'integer', minimum: 0, example: 1),
    ],
)]
final class VideoTranslationSummarySchema
{
}
