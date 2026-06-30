<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ResourceRequirement',
    required: ['type', 'weight'],
    properties: [
        new OA\Property(property: 'type', ref: '#/components/schemas/ResourceType'),
        new OA\Property(property: 'weight', type: 'integer', minimum: 1, example: 1),
    ],
)]
final class ResourceRequirementSchema
{
}
