<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'QualityScore',
    type: 'integer',
    minimum: 0,
    maximum: 100,
    example: 94,
)]
final class QualityScoreSchema
{
}
