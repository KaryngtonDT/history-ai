<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'VideoRenderList',
    required: ['videoId', 'renders'],
    properties: [
        new OA\Property(property: 'videoId', type: 'string', format: 'uuid'),
        new OA\Property(
            property: 'renders',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/VideoRenderSummary'),
        ),
    ],
)]
final class VideoRenderListSchema
{
}
