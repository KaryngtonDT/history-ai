<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'OptimizationProfile',
    type: 'string',
    enum: ['balanced', 'quality', 'speed', 'low_memory'],
    example: 'quality',
)]
final class OptimizationProfileSchema
{
}
