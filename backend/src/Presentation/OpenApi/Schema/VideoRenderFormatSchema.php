<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'VideoRenderFormat',
    type: 'string',
    enum: ['mp4', 'webm'],
    example: 'mp4',
)]
final class VideoRenderFormatSchema
{
}
