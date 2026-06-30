<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'GenerateVideoVoiceCloneRequest',
    properties: [
        new OA\Property(
            property: 'targetLanguages',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/TranslationLanguage'),
        ),
        new OA\Property(property: 'provider', ref: '#/components/schemas/VoiceCloneProvider'),
        new OA\Property(
            property: 'voiceMode',
            type: 'string',
            enum: ['generic', 'clone'],
            example: 'clone',
        ),
    ],
)]
final class GenerateVideoVoiceCloneRequestSchema
{
}
