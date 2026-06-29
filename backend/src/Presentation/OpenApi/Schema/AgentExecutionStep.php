<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AgentExecutionStep',
    required: ['order', 'tool', 'status', 'summary', 'metadata'],
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
            example: 'Semantic search found 3 relevant chunks.',
        ),
        new OA\Property(
            property: 'metadata',
            description: 'Tool-specific execution metadata (`object<string, mixed>`). SemanticSearch: `resultCount`, `topScore`. KnowledgeGraph: `nodeCount`, `edgeCount`. MultiDocumentChat: `messageCount`, `sourceCount`, `citationCount`. Missing conversation: `requiresConversation`.',
            type: 'object',
            additionalProperties: true,
            example: [
                'resultCount' => 3,
                'topScore' => 0.91,
            ],
        ),
    ],
)]
final class AgentExecutionStep
{
}
