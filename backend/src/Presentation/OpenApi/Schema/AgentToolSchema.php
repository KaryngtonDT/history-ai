<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AgentTool',
    type: 'string',
    description: 'Deterministic agent tool identifier.',
    enum: ['semantic_search', 'knowledge_graph', 'conversation_memory', 'multi_document_chat'],
    example: 'semantic_search',
)]
final class AgentToolSchema
{
}
