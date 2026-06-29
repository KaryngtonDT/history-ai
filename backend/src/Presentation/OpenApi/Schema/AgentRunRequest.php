<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AgentRunRequest',
    required: ['question'],
    properties: [
        new OA\Property(
            property: 'question',
            type: 'string',
            minLength: 1,
            maxLength: 2000,
            example: 'Compare Rome and Byzantium',
        ),
        new OA\Property(
            property: 'conversationId',
            type: 'string',
            format: 'uuid',
            nullable: true,
            example: '550e8400-e29b-41d4-a716-446655440001',
        ),
    ],
)]
final class AgentRunRequest
{
}
