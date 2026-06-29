<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Agent;

use App\Domain\Agent\AgentTool;
use App\Domain\Agent\AgentToolExecution;
use App\Domain\Agent\AgentToolExecutorInterface;
use App\Domain\Agent\ConversationMemoryExecution;
use App\Domain\Agent\ConversationMemoryResult;
use App\Domain\Agent\ConversationMemoryToolExecutorInterface;
use App\Infrastructure\Agent\ConversationMemoryAgentToolExecutor;
use PHPUnit\Framework\TestCase;

final class ConversationMemoryAgentToolExecutorTest extends TestCase
{
    public function testExecuteReturnsEmptyResultWhenConversationIdMissing(): void
    {
        $memoryExecutor = $this->createMock(ConversationMemoryToolExecutorInterface::class);
        $memoryExecutor->expects(self::never())->method('execute');

        $result = (new ConversationMemoryAgentToolExecutor($memoryExecutor))->execute(
            new AgentToolExecution(
                AgentTool::ConversationMemory,
                'What did we discuss earlier?',
                '550e8400-e29b-41d4-a716-446655440000',
            ),
        );

        self::assertSame(AgentTool::ConversationMemory, $result->tool());
        self::assertSame('No conversation memory.', $result->summary());
        self::assertSame([], $result->metadata());
    }

    public function testExecuteDelegatesToConversationMemoryToolExecutor(): void
    {
        $execution = new AgentToolExecution(
            AgentTool::ConversationMemory,
            'What did we discuss earlier?',
            '550e8400-e29b-41d4-a716-446655440000',
            '550e8400-e29b-41d4-a716-446655440001',
        );
        $expected = new ConversationMemoryResult(
            'Conversation memory contains 4 messages.',
            4,
            [
                'messageCount' => 4,
                'userMessages' => 2,
                'assistantMessages' => 2,
            ],
        );

        $memoryExecutor = $this->createMock(ConversationMemoryToolExecutorInterface::class);
        $memoryExecutor
            ->expects(self::once())
            ->method('execute')
            ->with(new ConversationMemoryExecution(
                '550e8400-e29b-41d4-a716-446655440001',
                'What did we discuss earlier?',
            ))
            ->willReturn($expected);

        $result = (new ConversationMemoryAgentToolExecutor($memoryExecutor))->execute($execution);

        self::assertSame(AgentTool::ConversationMemory, $result->tool());
        self::assertSame('Conversation memory contains 4 messages.', $result->summary());
        self::assertSame(
            [
                'messageCount' => 4,
                'userMessages' => 2,
                'assistantMessages' => 2,
            ],
            $result->metadata(),
        );
    }
}
