<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PipelineStage',
    required: ['stage', 'providerId'],
    properties: [
        new OA\Property(property: 'stage', ref: '#/components/schemas/PipelineStageType'),
        new OA\Property(property: 'providerId', type: 'string', example: 'ollama'),
    ],
)]
final class PipelineStageSchema
{
}
