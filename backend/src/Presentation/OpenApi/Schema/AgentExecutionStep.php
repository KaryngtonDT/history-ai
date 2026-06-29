<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AgentExecutionStep',
    required: ['order', 'tool', 'status', 'summary'],
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
            property: 'status',
            ref: '#/components/schemas/AgentExecutionStatus',
        ),
        new OA\Property(
            property: 'summary',
            type: 'string',
            example: 'Semantic search prepared.',
        ),
    ],
)]
final class AgentExecutionStep
{
}
