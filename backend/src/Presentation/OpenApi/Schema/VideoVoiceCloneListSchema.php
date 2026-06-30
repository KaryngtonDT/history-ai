<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'VideoVoiceCloneList',
    required: ['videoId', 'voiceClones'],
    properties: [
        new OA\Property(property: 'videoId', type: 'string', format: 'uuid'),
        new OA\Property(
            property: 'voiceClones',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/VideoVoiceCloneSummary'),
        ),
    ],
)]
final class VideoVoiceCloneListSchema
{
}
