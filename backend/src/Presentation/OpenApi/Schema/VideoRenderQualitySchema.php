<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'VideoRenderQuality',
    type: 'string',
    enum: ['preview', 'standard', 'high'],
    example: 'standard',
)]
final class VideoRenderQualitySchema
{
}
