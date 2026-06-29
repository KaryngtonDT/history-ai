<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Agent;

use App\Domain\Agent\ConversationMemoryExecution;
use App\Domain\Agent\ConversationMemoryResult;
use App\Domain\Agent\ConversationMemoryToolExecutorInterface;
use PHPUnit\Framework\TestCase;

final class ConversationMemoryToolExecutorInterfaceTest extends TestCase
{
    public function testExecutorInterfaceDefinesExecuteMethod(): void
    {
        $execution = new ConversationMemoryExecution(
            '550e8400-e29b-41d4-a716-446655440001',
            'What did we discuss earlier?',
        );
        $expectedResult = new ConversationMemoryResult(
            'Conversation memory contains 2 messages.',
            2,
            ['userMessages' => 1, 'assistantMessages' => 1],
        );

        $executor = $this->createMock(ConversationMemoryToolExecutorInterface::class);
        $executor
            ->expects(self::once())
            ->method('execute')
            ->with($execution)
            ->willReturn($expectedResult);

        self::assertSame($expectedResult, $executor->execute($execution));
    }
}
