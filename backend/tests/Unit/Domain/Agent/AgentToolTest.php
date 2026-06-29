<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Agent;

use App\Domain\Agent\AgentTool;
use PHPUnit\Framework\TestCase;

final class AgentToolTest extends TestCase
{
    public function testExposesAllSupportedTools(): void
    {
        self::assertSame(
            [
                'semantic_search',
                'knowledge_graph',
                'conversation_memory',
                'multi_document_chat',
            ],
            array_map(
                static fn (AgentTool $tool): string => $tool->value,
                AgentTool::cases(),
            ),
        );
    }

    public function testEnumCasesAreStable(): void
    {
        self::assertSame(AgentTool::SemanticSearch, AgentTool::from('semantic_search'));
        self::assertSame(AgentTool::KnowledgeGraph, AgentTool::from('knowledge_graph'));
        self::assertSame(AgentTool::ConversationMemory, AgentTool::from('conversation_memory'));
        self::assertSame(AgentTool::MultiDocumentChat, AgentTool::from('multi_document_chat'));
    }
}
