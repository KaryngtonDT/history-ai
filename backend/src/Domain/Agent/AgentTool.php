<?php

declare(strict_types=1);

namespace App\Domain\Agent;

enum AgentTool: string
{
    case SemanticSearch = 'semantic_search';
    case KnowledgeGraph = 'knowledge_graph';
    case ConversationMemory = 'conversation_memory';
    case MultiDocumentChat = 'multi_document_chat';
}
