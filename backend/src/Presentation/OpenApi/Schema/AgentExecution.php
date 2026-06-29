<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AgentExecution',
    required: ['plan', 'steps', 'finalSummary'],
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
    ],
)]
final class AgentExecution
{
}
