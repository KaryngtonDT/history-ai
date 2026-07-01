<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'SourceStatus',
    type: 'string',
    enum: ['uploaded', 'queued', 'processing', 'completed', 'failed'],
    example: 'queued',
)]
final class SourceStatusSchema
{
}
