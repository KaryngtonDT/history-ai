<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ResourceType',
    type: 'string',
    enum: ['cpu', 'gpu', 'io'],
    example: 'gpu',
)]
final class ResourceTypeSchema
{
}
