<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Agent;

use App\Domain\Agent\AgentTool;
use App\Domain\Agent\AgentToolExecution;
use PHPUnit\Framework\TestCase;

final class AgentToolExecutionTest extends TestCase
{
    private const string CONTENT_ID = '550e8400-e29b-41d4-a716-446655440000';
    private const string CONVERSATION_ID = '550e8400-e29b-41d4-a716-446655440001';

    public function testExposesToolQuestionContentIdAndOptionalConversationId(): void
    {
        $execution = new AgentToolExecution(
            AgentTool::KnowledgeGraph,
            'Compare Rome and Byzantium',
            self::CONTENT_ID,
            self::CONVERSATION_ID,
        );

        self::assertSame(AgentTool::KnowledgeGraph, $execution->tool());
        self::assertSame('Compare Rome and Byzantium', $execution->question());
        self::assertSame(self::CONTENT_ID, $execution->contentId());
        self::assertSame(self::CONVERSATION_ID, $execution->conversationId());
    }

    public function testAllowsMissingConversationId(): void
    {
        $execution = new AgentToolExecution(
            AgentTool::SemanticSearch,
            'What is Rome?',
            self::CONTENT_ID,
        );

        self::assertNull($execution->conversationId());
    }

    public function testIsImmutable(): void
    {
        $execution = new AgentToolExecution(
            AgentTool::MultiDocumentChat,
            'What is Rome?',
            self::CONTENT_ID,
        );

        self::assertSame(AgentTool::MultiDocumentChat, $execution->tool());
        self::assertSame('What is Rome?', $execution->question());
        self::assertSame(self::CONTENT_ID, $execution->contentId());
    }

    public function testEqualsComparesAllFields(): void
    {
        $first = new AgentToolExecution(
            AgentTool::ConversationMemory,
            'What did we discuss earlier?',
            self::CONTENT_ID,
            self::CONVERSATION_ID,
        );
        $second = new AgentToolExecution(
            AgentTool::ConversationMemory,
            'What did we discuss earlier?',
            self::CONTENT_ID,
            self::CONVERSATION_ID,
        );
        $differentConversation = new AgentToolExecution(
            AgentTool::ConversationMemory,
            'What did we discuss earlier?',
            self::CONTENT_ID,
        );

        self::assertTrue($first->equals($second));
        self::assertFalse($first->equals($differentConversation));
    }
}
