<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PipelineConfiguration',
    required: ['id', 'version', 'createdAt', 'updatedAt', 'stages'],
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'version', type: 'integer', example: 1),
        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updatedAt', type: 'string', format: 'date-time'),
        new OA\Property(
            property: 'stages',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/PipelineStage'),
        ),
    ],
)]
final class PipelineConfigurationSchema
{
}
