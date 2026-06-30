<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'GenerateVideoAudioRequest',
    properties: [
        new OA\Property(
            property: 'targetLanguages',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/TranslationLanguage'),
            example: ['french', 'german'],
        ),
        new OA\Property(
            property: 'provider',
            ref: '#/components/schemas/TextToSpeechProvider',
            nullable: true,
        ),
        new OA\Property(
            property: 'voiceId',
            type: 'string',
            example: 'female_01',
            nullable: true,
        ),
    ],
)]
final class GenerateVideoAudioRequestSchema
{
}
