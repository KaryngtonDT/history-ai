<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Agent;

use App\Application\Agent\Commands\RunAgentCommand;
use App\Application\Agent\Handlers\RunAgentHandler;
use App\Domain\Agent\AgentExecutionStatus;
use App\Domain\Agent\AgentTool;
use App\Domain\Agent\AgentToolExecution;
use App\Domain\Agent\AgentToolExecutionResult;
use App\Domain\Agent\AgentToolExecutorInterface;
use App\Domain\Agent\Exception\InvalidAgentPlanException;
use App\Domain\Chat\Exception\InvalidConversationIdException;
use App\Domain\Content\Exception\InvalidContentIdException;
use App\Infrastructure\Agent\DeterministicAgentPlanner;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class RunAgentHandlerTest extends TestCase
{
    private const string CONTENT_ID = '550e8400-e29b-41d4-a716-446655440000';
    private const string CONVERSATION_ID = '550e8400-e29b-41d4-a716-446655440001';

    private AgentToolExecutorInterface $toolExecutor;

    private RunAgentHandler $handler;

    protected function setUp(): void
    {
        $this->toolExecutor = $this->createMock(AgentToolExecutorInterface::class);
        $this->handler = new RunAgentHandler(
            new DeterministicAgentPlanner(),
            $this->toolExecutor,
        );
    }

    public function testExecutesDefaultPlanInOrder(): void
    {
        $this->toolExecutor
            ->expects(self::exactly(2))
            ->method('execute')
            ->willReturnCallback(static function (AgentToolExecution $execution): AgentToolExecutionResult {
                if (AgentTool::SemanticSearch === $execution->tool()) {
                    return new AgentToolExecutionResult(
                        AgentTool::SemanticSearch,
                        'Semantic search found 2 relevant chunks.',
                        ['resultCount' => 2, 'topScore' => 0.91],
                    );
                }

                return new AgentToolExecutionResult(
                    AgentTool::MultiDocumentChat,
                    'Multi-document chat requires a conversation.',
                    ['requiresConversation' => true],
                );
            });

        $result = ($this->handler)(new RunAgentCommand(self::CONTENT_ID, 'What is Rome?'));

        self::assertSame(
            ['semantic_search', 'multi_document_chat'],
            array_map(static fn ($step) => $step->tool, $result->plan),
        );
        self::assertSame(
            [0, 1],
            array_map(static fn ($step) => $step->order, $result->steps),
        );
        self::assertSame(
            array_fill(0, 2, AgentExecutionStatus::Completed->value),
            array_map(static fn ($step) => $step->status, $result->steps),
        );
        self::assertSame('Semantic search found 2 relevant chunks.', $result->steps[0]->summary);
        self::assertSame(['resultCount' => 2, 'topScore' => 0.91], $result->steps[0]->metadata);
        self::assertSame('Multi-document chat requires a conversation.', $result->steps[1]->summary);
        self::assertSame(['requiresConversation' => true], $result->steps[1]->metadata);
        self::assertSame('Agent workflow completed.', $result->finalSummary);
    }

    public function testExecutesComparisonPlanWithKnowledgeGraphStep(): void
    {
        $this->toolExecutor
            ->expects(self::exactly(3))
            ->method('execute')
            ->willReturnCallback(static function (AgentToolExecution $execution): AgentToolExecutionResult {
                return match ($execution->tool()) {
                    AgentTool::SemanticSearch => new AgentToolExecutionResult(
                        AgentTool::SemanticSearch,
                        'Semantic search found 1 relevant chunks.',
                        ['resultCount' => 1, 'topScore' => 0.85],
                    ),
                    AgentTool::KnowledgeGraph => new AgentToolExecutionResult(
                        AgentTool::KnowledgeGraph,
                        'Knowledge graph contains 3 nodes and 3 relationships.',
                        ['nodeCount' => 3, 'edgeCount' => 3],
                    ),
                    AgentTool::MultiDocumentChat => new AgentToolExecutionResult(
                        AgentTool::MultiDocumentChat,
                        'Multi-document chat requires a conversation.',
                        ['requiresConversation' => true],
                    ),
                    default => new AgentToolExecutionResult(
                        $execution->tool(),
                        'No execution.',
                        [],
                    ),
                };
            });

        $result = ($this->handler)(new RunAgentCommand(self::CONTENT_ID, 'Compare Rome versus Byzantium'));

        self::assertSame(
            ['semantic_search', 'knowledge_graph', 'multi_document_chat'],
            array_map(static fn ($step) => $step->tool, $result->plan),
        );
        self::assertSame('Knowledge graph contains 3 nodes and 3 relationships.', $result->steps[1]->summary);
        self::assertSame(['nodeCount' => 3, 'edgeCount' => 3], $result->steps[1]->metadata);
    }

    public function testExecutesMemoryPlanWithConversationMemoryStep(): void
    {
        $this->toolExecutor
            ->expects(self::exactly(3))
            ->method('execute')
            ->willReturnCallback(static function (AgentToolExecution $execution): AgentToolExecutionResult {
                return match ($execution->tool()) {
                    AgentTool::SemanticSearch => new AgentToolExecutionResult(
                        AgentTool::SemanticSearch,
                        'Semantic search found no relevant chunks.',
                        ['resultCount' => 0],
                    ),
                    AgentTool::ConversationMemory => new AgentToolExecutionResult(
                        AgentTool::ConversationMemory,
                        'No conversation memory.',
                        [],
                    ),
                    default => new AgentToolExecutionResult(
                        AgentTool::MultiDocumentChat,
                        'Multi-document chat requires a conversation.',
                        ['requiresConversation' => true],
                    ),
                };
            });

        $result = ($this->handler)(new RunAgentCommand(self::CONTENT_ID, 'What did we discuss earlier?'));

        self::assertSame(
            ['semantic_search', 'conversation_memory', 'multi_document_chat'],
            array_map(static fn ($step) => $step->tool, $result->plan),
        );
        self::assertSame('No conversation memory.', $result->steps[1]->summary);
    }

    public function testPassesConversationIdToToolExecutor(): void
    {
        $this->toolExecutor
            ->expects(self::exactly(2))
            ->method('execute')
            ->with(self::callback(static function (AgentToolExecution $execution): bool {
                return self::CONVERSATION_ID === $execution->conversationId();
            }))
            ->willReturn(new AgentToolExecutionResult(
                AgentTool::SemanticSearch,
                'Semantic search found no relevant chunks.',
                ['resultCount' => 0],
            ));

        ($this->handler)(new RunAgentCommand(
            self::CONTENT_ID,
            'What is Rome?',
            self::CONVERSATION_ID,
        ));
    }

    public function testMarksFailedStepAndContinuesExecution(): void
    {
        $this->toolExecutor
            ->expects(self::exactly(2))
            ->method('execute')
            ->willReturnCallback(static function (AgentToolExecution $execution): AgentToolExecutionResult {
                if (AgentTool::SemanticSearch === $execution->tool()) {
                    throw new RuntimeException('Semantic search failed.');
                }

                return new AgentToolExecutionResult(
                    AgentTool::MultiDocumentChat,
                    'Multi-document chat requires a conversation.',
                    ['requiresConversation' => true],
                );
            });

        $result = ($this->handler)(new RunAgentCommand(self::CONTENT_ID, 'What is Rome?'));

        self::assertSame(AgentExecutionStatus::Failed->value, $result->steps[0]->status);
        self::assertSame('Tool execution failed.', $result->steps[0]->summary);
        self::assertSame([], $result->steps[0]->metadata);
        self::assertSame(AgentExecutionStatus::Completed->value, $result->steps[1]->status);
        self::assertSame('Agent workflow completed.', $result->finalSummary);
    }

    public function testAcceptsOptionalConversationId(): void
    {
        $this->toolExecutor
            ->expects(self::exactly(2))
            ->method('execute')
            ->willReturn(new AgentToolExecutionResult(
                AgentTool::SemanticSearch,
                'Semantic search found no relevant chunks.',
                ['resultCount' => 0],
            ));

        $result = ($this->handler)(new RunAgentCommand(
            self::CONTENT_ID,
            'What is Rome?',
            self::CONVERSATION_ID,
        ));

        self::assertSame(2, count($result->steps));
        self::assertSame('Agent workflow completed.', $result->finalSummary);
    }

    public function testIgnoresBlankConversationId(): void
    {
        $this->toolExecutor
            ->expects(self::exactly(2))
            ->method('execute')
            ->with(self::callback(static fn (AgentToolExecution $execution): bool => null === $execution->conversationId()))
            ->willReturn(new AgentToolExecutionResult(
                AgentTool::SemanticSearch,
                'Semantic search found no relevant chunks.',
                ['resultCount' => 0],
            ));

        $result = ($this->handler)(new RunAgentCommand(
            self::CONTENT_ID,
            'What is Rome?',
            '   ',
        ));

        self::assertSame(2, count($result->steps));
    }

    public function testRejectsInvalidContentId(): void
    {
        $this->toolExecutor->expects(self::never())->method('execute');

        $this->expectException(InvalidContentIdException::class);

        ($this->handler)(new RunAgentCommand('not-a-valid-uuid', 'What is Rome?'));
    }

    public function testRejectsInvalidConversationId(): void
    {
        $this->toolExecutor->expects(self::never())->method('execute');

        $this->expectException(InvalidConversationIdException::class);

        ($this->handler)(new RunAgentCommand(
            self::CONTENT_ID,
            'What is Rome?',
            'not-a-valid-uuid',
        ));
    }

    public function testRejectsEmptyQuestion(): void
    {
        $this->toolExecutor->expects(self::never())->method('execute');

        $this->expectException(InvalidAgentPlanException::class);
        $this->expectExceptionMessage('Agent question cannot be empty');

        ($this->handler)(new RunAgentCommand(self::CONTENT_ID, '   '));
    }

    public function testProducesDeterministicExecutionTrace(): void
    {
        $this->toolExecutor
            ->method('execute')
            ->willReturnCallback(static function (AgentToolExecution $execution): AgentToolExecutionResult {
                return match ($execution->tool()) {
                    AgentTool::SemanticSearch => new AgentToolExecutionResult(
                        AgentTool::SemanticSearch,
                        'Semantic search found 1 relevant chunks.',
                        ['resultCount' => 1, 'topScore' => 0.9],
                    ),
                    AgentTool::KnowledgeGraph => new AgentToolExecutionResult(
                        AgentTool::KnowledgeGraph,
                        'Knowledge graph is empty.',
                        ['nodeCount' => 0, 'edgeCount' => 0],
                    ),
                    default => new AgentToolExecutionResult(
                        AgentTool::MultiDocumentChat,
                        'Multi-document chat requires a conversation.',
                        ['requiresConversation' => true],
                    ),
                };
            });

        $command = new RunAgentCommand(self::CONTENT_ID, 'Compare Rome versus Byzantium');

        $first = ($this->handler)($command);
        $second = ($this->handler)($command);

        self::assertSame(
            array_map(static fn ($step) => [$step->order, $step->tool, $step->description], $first->plan),
            array_map(static fn ($step) => [$step->order, $step->tool, $step->description], $second->plan),
        );
        self::assertSame(
            array_map(static fn ($step) => $step->summary, $first->steps),
            array_map(static fn ($step) => $step->summary, $second->steps),
        );
        self::assertSame($first->finalSummary, $second->finalSummary);
    }
}
