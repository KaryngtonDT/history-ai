<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'VoiceProfile',
    required: ['profileId', 'sourceLanguage', 'duration', 'sampleRate'],
    properties: [
        new OA\Property(property: 'profileId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'sourceLanguage', ref: '#/components/schemas/TranslationLanguage'),
        new OA\Property(property: 'duration', type: 'number', format: 'float', example: 201.5),
        new OA\Property(property: 'sampleRate', type: 'integer', example: 44100),
    ],
)]
final class VoiceProfileSchema
{
}
