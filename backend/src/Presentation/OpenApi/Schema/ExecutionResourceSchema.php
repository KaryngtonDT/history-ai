<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ExecutionResource',
    required: ['type', 'running', 'pending', 'maxConcurrency'],
    properties: [
        new OA\Property(property: 'type', ref: '#/components/schemas/ResourceType'),
        new OA\Property(property: 'running', type: 'integer', minimum: 0, example: 1),
        new OA\Property(property: 'pending', type: 'integer', minimum: 0, example: 2),
        new OA\Property(property: 'maxConcurrency', type: 'integer', minimum: 1, example: 1),
    ],
)]
final class ExecutionResourceSchema
{
}
