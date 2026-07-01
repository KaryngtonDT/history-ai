<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'YouTubeMetadata',
    required: ['title'],
    properties: [
        new OA\Property(property: 'title', type: 'string'),
        new OA\Property(property: 'durationSeconds', type: 'integer', nullable: true),
        new OA\Property(property: 'thumbnailUrl', type: 'string', nullable: true),
        new OA\Property(property: 'language', type: 'string', nullable: true),
        new OA\Property(property: 'channelName', type: 'string', nullable: true),
    ],
)]
final class YouTubeMetadataSchema
{
}

#[OA\Schema(
    schema: 'ImportYouTubeResponse',
    required: ['youtubeId', 'videoId', 'status', 'url', 'metadata'],
    properties: [
        new OA\Property(property: 'youtubeId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'videoId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'status', ref: '#/components/schemas/VideoStatus'),
        new OA\Property(property: 'url', type: 'string'),
        new OA\Property(property: 'metadata', ref: '#/components/schemas/YouTubeMetadata'),
    ],
)]
final class ImportYouTubeResponseSchema
{
}

#[OA\Schema(
    schema: 'YouTubeImportResponse',
    required: ['youtubeId', 'videoId', 'url', 'videoStatus', 'importedAt', 'metadata'],
    properties: [
        new OA\Property(property: 'youtubeId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'videoId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'url', type: 'string'),
        new OA\Property(property: 'videoStatus', ref: '#/components/schemas/VideoStatus'),
        new OA\Property(property: 'importedAt', type: 'string', format: 'date-time'),
        new OA\Property(property: 'metadata', ref: '#/components/schemas/YouTubeMetadata'),
    ],
)]
final class YouTubeImportDetailResponseSchema
{
}
