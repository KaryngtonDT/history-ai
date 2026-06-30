<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'BatchJobStatus',
    type: 'string',
    enum: ['pending', 'running', 'completed', 'partial_failure', 'failed'],
)]
final class BatchJobStatusSchema
{
}
