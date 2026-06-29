<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'VideoStatus',
    type: 'string',
    description: 'Lifecycle status of a video processing job.',
    enum: ['uploaded', 'queued', 'processing', 'completed', 'failed'],
    example: 'queued',
)]
final class VideoStatusSchema
{
}
