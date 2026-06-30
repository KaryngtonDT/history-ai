<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'LipSyncArtifact',
    required: [
        'videoId',
        'artifactId',
        'clonedAudioId',
        'targetLanguage',
        'provider',
        'synchronizedVideoId',
        'duration',
        'originalVideoUrl',
        'syncedVideoUrl',
    ],
    properties: [
        new OA\Property(property: 'videoId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'artifactId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'clonedAudioId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'targetLanguage', ref: '#/components/schemas/TranslationLanguage'),
        new OA\Property(property: 'provider', ref: '#/components/schemas/LipSyncProvider'),
        new OA\Property(property: 'synchronizedVideoId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'duration', type: 'number', format: 'float', example: 120.5),
        new OA\Property(property: 'originalVideoUrl', type: 'string', example: '/api/videos/{videoId}/stream'),
        new OA\Property(property: 'syncedVideoUrl', type: 'string', example: '/api/videos/{videoId}/lip-sync/french/stream'),
    ],
)]
final class LipSyncArtifactSchema
{
}
