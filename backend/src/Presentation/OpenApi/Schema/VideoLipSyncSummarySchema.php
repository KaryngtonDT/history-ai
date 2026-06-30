<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'VideoLipSyncSummary',
    required: [
        'videoId',
        'artifactId',
        'clonedAudioId',
        'targetLanguage',
        'provider',
        'synchronizedVideoId',
        'duration',
        'syncedVideoUrl',
    ],
    properties: [
        new OA\Property(property: 'videoId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'artifactId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'clonedAudioId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'targetLanguage', ref: '#/components/schemas/TranslationLanguage'),
        new OA\Property(property: 'provider', ref: '#/components/schemas/LipSyncProvider'),
        new OA\Property(property: 'synchronizedVideoId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'duration', type: 'number', format: 'float'),
        new OA\Property(property: 'syncedVideoUrl', type: 'string'),
    ],
)]
final class VideoLipSyncSummarySchema
{
}
