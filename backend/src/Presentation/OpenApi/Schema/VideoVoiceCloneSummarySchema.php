<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'VideoVoiceCloneSummary',
    required: [
        'videoId',
        'artifactId',
        'sourceAudioId',
        'clonedAudioId',
        'targetLanguage',
        'provider',
        'duration',
        'sampleRate',
    ],
    properties: [
        new OA\Property(property: 'videoId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'artifactId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'sourceAudioId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'clonedAudioId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'targetLanguage', ref: '#/components/schemas/TranslationLanguage'),
        new OA\Property(property: 'provider', ref: '#/components/schemas/VoiceCloneProvider'),
        new OA\Property(property: 'duration', type: 'number', format: 'float'),
        new OA\Property(property: 'sampleRate', type: 'integer'),
    ],
)]
final class VideoVoiceCloneSummarySchema
{
}
