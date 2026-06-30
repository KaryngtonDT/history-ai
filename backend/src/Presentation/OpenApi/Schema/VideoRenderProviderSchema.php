<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'VideoRenderProvider',
    type: 'string',
    enum: ['ffmpeg', 'mock'],
    example: 'ffmpeg',
)]
final class VideoRenderProviderSchema
{
}
