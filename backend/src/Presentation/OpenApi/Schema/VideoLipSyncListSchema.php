<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'VideoLipSyncList',
    required: ['videoId', 'lipSyncs'],
    properties: [
        new OA\Property(property: 'videoId', type: 'string', format: 'uuid'),
        new OA\Property(
            property: 'lipSyncs',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/VideoLipSyncSummary'),
        ),
    ],
)]
final class VideoLipSyncListSchema
{
}
