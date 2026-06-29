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

        $fallbackExecutor = $this->createMock(AgentToolExecutorInterface::class);
        $fallbackExecutor->expects(self::never())->method('execute');

        $result = (new CompositeAgentToolExecutor($semanticExecutor, $fallbackExecutor))->execute($execution);

        self::assertSame($expected, $result);
    }

    public function testRoutesNonSemanticToolsToNullAgentToolExecutor(): void
    {
        $execution = new AgentToolExecution(
            AgentTool::KnowledgeGraph,
            'Compare Rome versus Byzantium',
            '550e8400-e29b-41d4-a716-446655440000',
        );

        $semanticExecutor = $this->createMock(AgentToolExecutorInterface::class);
        $semanticExecutor->expects(self::never())->method('execute');

        $fallbackExecutor = $this->createMock(AgentToolExecutorInterface::class);
        $fallbackExecutor
            ->expects(self::once())
            ->method('execute')
            ->with($execution)
            ->willReturn(new AgentToolExecutionResult(
                AgentTool::KnowledgeGraph,
                'No execution.',
                [],
            ));

        $result = (new CompositeAgentToolExecutor($semanticExecutor, $fallbackExecutor))->execute($execution);

        self::assertSame('No execution.', $result->summary());
        self::assertSame([], $result->metadata());
    }
}
