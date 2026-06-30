<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AudioArtifact',
    required: [
        'videoId',
        'audioId',
        'translationId',
        'targetLanguage',
        'provider',
        'voiceId',
        'voiceDisplayName',
        'voiceLanguage',
        'voiceGender',
        'duration',
        'format',
        'downloadUrl',
    ],
    properties: [
        new OA\Property(property: 'videoId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'audioId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'translationId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'targetLanguage', ref: '#/components/schemas/TranslationLanguage'),
        new OA\Property(property: 'provider', ref: '#/components/schemas/TextToSpeechProvider'),
        new OA\Property(property: 'voiceId', type: 'string', example: 'female_01'),
        new OA\Property(property: 'voiceDisplayName', type: 'string', example: 'Female 01'),
        new OA\Property(property: 'voiceLanguage', ref: '#/components/schemas/VoiceLanguage'),
        new OA\Property(property: 'voiceGender', ref: '#/components/schemas/VoiceGender'),
        new OA\Property(property: 'duration', type: 'number', format: 'float', example: 201.5),
        new OA\Property(property: 'format', type: 'string', example: 'wav'),
        new OA\Property(property: 'downloadUrl', type: 'string', example: '/api/videos/{videoId}/audio/french/stream'),
    ],
)]
final class AudioArtifactSchema
{
}
