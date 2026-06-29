<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Agent;

use App\Domain\Agent\AgentTool;
use App\Domain\Agent\AgentToolExecution;
use App\Domain\Agent\AgentToolExecutorInterface;
use App\Infrastructure\Agent\NullAgentToolExecutor;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class NullAgentToolExecutorTest extends TestCase
{
    private NullAgentToolExecutor $executor;

    protected function setUp(): void
    {
        $this->executor = new NullAgentToolExecutor();
    }

    public function testImplementsAgentToolExecutorInterface(): void
    {
        self::assertInstanceOf(AgentToolExecutorInterface::class, $this->executor);
    }

    #[DataProvider('agentToolProvider')]
    public function testExecuteReturnsNoExecutionSummaryForEveryTool(AgentTool $tool): void
    {
        $execution = new AgentToolExecution(
            $tool,
            'Compare Rome and Byzantium',
            '550e8400-e29b-41d4-a716-446655440000',
            '550e8400-e29b-41d4-a716-446655440001',
        );

        $result = $this->executor->execute($execution);

        self::assertSame($tool, $result->tool());
        self::assertSame('No execution.', $result->summary());
        self::assertSame([], $result->metadata());
    }

    public function testExecutePreservesToolFromExecution(): void
    {
        $execution = new AgentToolExecution(
            AgentTool::KnowledgeGraph,
            'Compare Rome versus Byzantium',
            '550e8400-e29b-41d4-a716-446655440000',
        );

        $result = $this->executor->execute($execution);

        self::assertSame($execution->tool(), $result->tool());
    }

    public function testExecuteReturnsEmptyMetadata(): void
    {
        $execution = new AgentToolExecution(
            AgentTool::ConversationMemory,
            'What did we discuss earlier?',
            '550e8400-e29b-41d4-a716-446655440000',
        );

        $result = $this->executor->execute($execution);

        self::assertSame([], $result->metadata());
    }

    /**
     * @return iterable<string, array{AgentTool}>
     */
    public static function agentToolProvider(): iterable
    {
        yield 'semantic_search' => [AgentTool::SemanticSearch];
        yield 'knowledge_graph' => [AgentTool::KnowledgeGraph];
        yield 'conversation_memory' => [AgentTool::ConversationMemory];
        yield 'multi_document_chat' => [AgentTool::MultiDocumentChat];
    }
}
