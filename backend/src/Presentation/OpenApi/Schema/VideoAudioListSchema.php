<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'VideoAudioList',
    required: ['videoId', 'audio'],
    properties: [
        new OA\Property(property: 'videoId', type: 'string', format: 'uuid'),
        new OA\Property(
            property: 'audio',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/VideoAudioSummary'),
        ),
    ],
)]
final class VideoAudioListSchema
{
}
