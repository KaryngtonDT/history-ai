<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Agent;

use App\Domain\Agent\AgentTool;
use App\Domain\Agent\AgentToolExecution;
use App\Domain\Agent\AgentToolExecutionResult;
use App\Domain\Agent\AgentToolExecutorInterface;
use PHPUnit\Framework\TestCase;

final class AgentToolExecutorInterfaceTest extends TestCase
{
    public function testExecutorInterfaceDefinesExecuteMethod(): void
    {
        $execution = new AgentToolExecution(
            AgentTool::SemanticSearch,
            'What is Rome?',
            '550e8400-e29b-41d4-a716-446655440000',
        );
        $expectedResult = new AgentToolExecutionResult(
            AgentTool::SemanticSearch,
            'Semantic search prepared.',
            ['resultCount' => 1],
        );

        $executor = $this->createMock(AgentToolExecutorInterface::class);
        $executor
            ->expects(self::once())
            ->method('execute')
            ->with($execution)
            ->willReturn($expectedResult);

        self::assertSame($expectedResult, $executor->execute($execution));
    }
}
