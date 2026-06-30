<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'VoiceCloneArtifact',
    required: [
        'videoId',
        'artifactId',
        'sourceAudioId',
        'clonedAudioId',
        'targetLanguage',
        'provider',
        'sourceLanguage',
        'duration',
        'sampleRate',
        'originalAudioUrl',
        'clonedAudioUrl',
    ],
    properties: [
        new OA\Property(property: 'videoId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'artifactId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'sourceAudioId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'clonedAudioId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'targetLanguage', ref: '#/components/schemas/TranslationLanguage'),
        new OA\Property(property: 'provider', ref: '#/components/schemas/VoiceCloneProvider'),
        new OA\Property(property: 'sourceLanguage', ref: '#/components/schemas/TranslationLanguage'),
        new OA\Property(property: 'duration', type: 'number', format: 'float', example: 201.5),
        new OA\Property(property: 'sampleRate', type: 'integer', example: 44100),
        new OA\Property(property: 'originalAudioUrl', type: 'string', example: '/api/videos/{videoId}/audio/french/stream'),
        new OA\Property(property: 'clonedAudioUrl', type: 'string', example: '/api/videos/{videoId}/voice-clone/french/stream'),
    ],
)]
final class VoiceCloneArtifactSchema
{
}
