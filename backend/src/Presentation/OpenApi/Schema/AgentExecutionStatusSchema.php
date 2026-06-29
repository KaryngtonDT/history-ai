<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AgentExecutionStatus',
    type: 'string',
    description: 'Execution status for a planned agent step.',
    enum: ['completed', 'skipped', 'failed'],
    example: 'completed',
)]
final class AgentExecutionStatusSchema
{
}
