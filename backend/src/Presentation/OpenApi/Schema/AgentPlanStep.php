<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AgentPlanStep',
    required: ['order', 'tool', 'description'],
    properties: [
        new OA\Property(
            property: 'order',
            type: 'integer',
            minimum: 0,
            example: 0,
        ),
        new OA\Property(
            property: 'tool',
            ref: '#/components/schemas/AgentTool',
        ),
        new OA\Property(
            property: 'description',
            type: 'string',
            example: 'Retrieve relevant document chunks for the question',
        ),
    ],
)]
final class AgentPlanStep
{
}
