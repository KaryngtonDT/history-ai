<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AgentExecution',
    required: ['plan', 'steps', 'finalSummary', 'metadata'],
    properties: [
        new OA\Property(
            property: 'plan',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/AgentPlanStep'),
        ),
        new OA\Property(
            property: 'steps',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/AgentExecutionStep'),
        ),
        new OA\Property(
            property: 'finalSummary',
            type: 'string',
            example: 'Agent workflow completed.',
        ),
        new OA\Property(
            property: 'metadata',
            description: 'Aggregated metadata from all executed tool steps (`object<string, mixed>`). Later tools overwrite duplicate keys.',
            type: 'object',
            additionalProperties: true,
            example: [
                'resultCount' => 3,
                'topScore' => 0.91,
                'nodeCount' => 12,
                'edgeCount' => 18,
                'messageCount' => 9,
                'sourceCount' => 3,
                'citationCount' => 3,
            ],
        ),
    ],
)]
final class AgentExecution
{
}
