<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'SavePipelineConfigurationRequest',
    required: ['stages'],
    properties: [
        new OA\Property(
            property: 'stages',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/PipelineStage'),
        ),
    ],
)]
final class SavePipelineConfigurationRequestSchema
{
}
