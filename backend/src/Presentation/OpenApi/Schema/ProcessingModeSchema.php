<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProcessingMode',
    type: 'string',
    enum: ['manual', 'automatic'],
    example: 'automatic',
)]
final class ProcessingModeSchema
{
}
