<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Agent;

use App\Domain\Agent\AgentTool;
use App\Domain\Agent\AgentToolExecution;
use App\Domain\Agent\AgentToolExecutionResult;
use App\Domain\Agent\AgentToolExecutorInterface;
use App\Infrastructure\Agent\CompositeAgentToolExecutor;
use App\Infrastructure\Agent\NullAgentToolExecutor;
use PHPUnit\Framework\TestCase;

final class CompositeAgentToolExecutorTest extends TestCase
{
    public function testImplementsAgentToolExecutorInterface(): void
    {
        $composite = new CompositeAgentToolExecutor(
            $this->createMock(AgentToolExecutorInterface::class),
            $this->createMock(AgentToolExecutorInterface::class),
            $this->createMock(AgentToolExecutorInterface::class),
            $this->createMock(AgentToolExecutorInterface::class),
            new NullAgentToolExecutor(),
        );

        self::assertInstanceOf(AgentToolExecutorInterface::class, $composite);
    }

    public function testRoutesSemanticSearchToSemanticSearchToolExecutor(): void
    {
        $execution = new AgentToolExecution(
            AgentTool::SemanticSearch,
            'What is Rome?',
            '550e8400-e29b-41d4-a716-446655440000',
        );
        $expected = new AgentToolExecutionResult(
            AgentTool::SemanticSearch,
            'Semantic search found 2 relevant chunks.',
            ['resultCount' => 2, 'topScore' => 0.88],
        );

        $semanticExecutor = $this->createMock(AgentToolExecutorInterface::class);
        $semanticExecutor
            ->expects(self::once())
            ->method('execute')
            ->with($execution)
            ->willReturn($expected);

        $knowledgeGraphExecutor = $this->createMock(AgentToolExecutorInterface::class);
        $knowledgeGraphExecutor->expects(self::never())->method('execute');

        $conversationMemoryExecutor = $this->createMock(AgentToolExecutorInterface::class);
        $conversationMemoryExecutor->expects(self::never())->method('execute');

        $multiDocumentChatExecutor = $this->createMock(AgentToolExecutorInterface::class);
        $multiDocumentChatExecutor->expects(self::never())->method('execute');

        $fallbackExecutor = $this->createMock(AgentToolExecutorInterface::class);
        $fallbackExecutor->expects(self::never())->method('execute');

        $result = (new CompositeAgentToolExecutor(
            $semanticExecutor,
            $knowledgeGraphExecutor,
            $conversationMemoryExecutor,
            $multiDocumentChatExecutor,
            $fallbackExecutor,
        ))->execute($execution);

        self::assertSame($expected, $result);
    }

    public function testRoutesKnowledgeGraphToKnowledgeGraphToolExecutor(): void
    {
        $execution = new AgentToolExecution(
            AgentTool::KnowledgeGraph,
            'Compare Rome versus Byzantium',
            '550e8400-e29b-41d4-a716-446655440000',
        );
        $expected = new AgentToolExecutionResult(
            AgentTool::KnowledgeGraph,
            'Knowledge graph contains 18 nodes and 24 relationships.',
            ['nodeCount' => 18, 'edgeCount' => 24],
        );

        $semanticExecutor = $this->createMock(AgentToolExecutorInterface::class);
        $semanticExecutor->expects(self::never())->method('execute');

        $knowledgeGraphExecutor = $this->createMock(AgentToolExecutorInterface::class);
        $knowledgeGraphExecutor
            ->expects(self::once())
            ->method('execute')
            ->with($execution)
            ->willReturn($expected);

        $conversationMemoryExecutor = $this->createMock(AgentToolExecutorInterface::class);
        $conversationMemoryExecutor->expects(self::never())->method('execute');

        $multiDocumentChatExecutor = $this->createMock(AgentToolExecutorInterface::class);
        $multiDocumentChatExecutor->expects(self::never())->method('execute');

        $fallbackExecutor = $this->createMock(AgentToolExecutorInterface::class);
        $fallbackExecutor->expects(self::never())->method('execute');

        $result = (new CompositeAgentToolExecutor(
            $semanticExecutor,
            $knowledgeGraphExecutor,
            $conversationMemoryExecutor,
            $multiDocumentChatExecutor,
            $fallbackExecutor,
        ))->execute($execution);

        self::assertSame($expected, $result);
    }

    public function testRoutesConversationMemoryToConversationMemoryToolExecutor(): void
    {
        $execution = new AgentToolExecution(
            AgentTool::ConversationMemory,
            'What did we discuss earlier?',
            '550e8400-e29b-41d4-a716-446655440000',
            '550e8400-e29b-41d4-a716-446655440001',
        );
        $expected = new AgentToolExecutionResult(
            AgentTool::ConversationMemory,
            'Conversation memory contains 4 messages.',
            [
                'messageCount' => 4,
                'userMessages' => 2,
                'assistantMessages' => 2,
            ],
        );

        $semanticExecutor = $this->createMock(AgentToolExecutorInterface::class);
        $semanticExecutor->expects(self::never())->method('execute');

        $knowledgeGraphExecutor = $this->createMock(AgentToolExecutorInterface::class);
        $knowledgeGraphExecutor->expects(self::never())->method('execute');

        $conversationMemoryExecutor = $this->createMock(AgentToolExecutorInterface::class);
        $conversationMemoryExecutor
            ->expects(self::once())
            ->method('execute')
            ->with($execution)
            ->willReturn($expected);

        $multiDocumentChatExecutor = $this->createMock(AgentToolExecutorInterface::class);
        $multiDocumentChatExecutor->expects(self::never())->method('execute');

        $fallbackExecutor = $this->createMock(AgentToolExecutorInterface::class);
        $fallbackExecutor->expects(self::never())->method('execute');

        $result = (new CompositeAgentToolExecutor(
            $semanticExecutor,
            $knowledgeGraphExecutor,
            $conversationMemoryExecutor,
            $multiDocumentChatExecutor,
            $fallbackExecutor,
        ))->execute($execution);

        self::assertSame($expected, $result);
    }

    public function testRoutesMultiDocumentChatToMultiDocumentChatToolExecutor(): void
    {
        $execution = new AgentToolExecution(
            AgentTool::MultiDocumentChat,
            'What is Rome?',
            '550e8400-e29b-41d4-a716-446655440000',
            '550e8400-e29b-41d4-a716-446655440001',
        );
        $expected = new AgentToolExecutionResult(
            AgentTool::MultiDocumentChat,
            'Multi-document chat generated an answer.',
            ['messageCount' => 2, 'sourceCount' => 1, 'citationCount' => 1],
        );

        $semanticExecutor = $this->createMock(AgentToolExecutorInterface::class);
        $semanticExecutor->expects(self::never())->method('execute');

        $knowledgeGraphExecutor = $this->createMock(AgentToolExecutorInterface::class);
        $knowledgeGraphExecutor->expects(self::never())->method('execute');

        $conversationMemoryExecutor = $this->createMock(AgentToolExecutorInterface::class);
        $conversationMemoryExecutor->expects(self::never())->method('execute');

        $multiDocumentChatExecutor = $this->createMock(AgentToolExecutorInterface::class);
        $multiDocumentChatExecutor
            ->expects(self::once())
            ->method('execute')
            ->with($execution)
            ->willReturn($expected);

        $fallbackExecutor = $this->createMock(AgentToolExecutorInterface::class);
        $fallbackExecutor->expects(self::never())->method('execute');

        $result = (new CompositeAgentToolExecutor(
            $semanticExecutor,
            $knowledgeGraphExecutor,
            $conversationMemoryExecutor,
            $multiDocumentChatExecutor,
            $fallbackExecutor,
        ))->execute($execution);

        self::assertSame($expected, $result);
    }
}
