<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Agent;

use App\Application\Agent\Commands\RunAgentCommand;
use App\Application\Agent\Handlers\RunAgentHandler;
use App\Domain\Agent\AgentExecutionStatus;
use App\Domain\Agent\Exception\InvalidAgentPlanException;
use App\Domain\Chat\Exception\InvalidConversationIdException;
use App\Domain\Content\Exception\InvalidContentIdException;
use App\Infrastructure\Agent\DeterministicAgentPlanner;
use PHPUnit\Framework\TestCase;

final class RunAgentHandlerTest extends TestCase
{
    private const string CONTENT_ID = '550e8400-e29b-41d4-a716-446655440000';
    private const string CONVERSATION_ID = '550e8400-e29b-41d4-a716-446655440001';

    private RunAgentHandler $handler;

    protected function setUp(): void
    {
        $this->handler = new RunAgentHandler(new DeterministicAgentPlanner());
    }

    public function testExecutesDefaultPlanInOrder(): void
    {
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
        self::assertSame('Semantic search prepared.', $result->steps[0]->summary);
        self::assertSame('Multi-document chat prepared.', $result->steps[1]->summary);
        self::assertSame('Agent workflow completed.', $result->finalSummary);
    }

    public function testExecutesComparisonPlanWithKnowledgeGraphStep(): void
    {
        $result = ($this->handler)(new RunAgentCommand(self::CONTENT_ID, 'Compare Rome versus Byzantium'));

        self::assertSame(
            ['semantic_search', 'knowledge_graph', 'multi_document_chat'],
            array_map(static fn ($step) => $step->tool, $result->plan),
        );
        self::assertSame('Knowledge graph exploration prepared.', $result->steps[1]->summary);
    }

    public function testExecutesMemoryPlanWithConversationMemoryStep(): void
    {
        $result = ($this->handler)(new RunAgentCommand(self::CONTENT_ID, 'What did we discuss earlier?'));

        self::assertSame(
            ['semantic_search', 'conversation_memory', 'multi_document_chat'],
            array_map(static fn ($step) => $step->tool, $result->plan),
        );
        self::assertSame('Conversation memory prepared.', $result->steps[1]->summary);
    }

    public function testAcceptsOptionalConversationId(): void
    {
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
        $result = ($this->handler)(new RunAgentCommand(
            self::CONTENT_ID,
            'What is Rome?',
            '   ',
        ));

        self::assertSame(2, count($result->steps));
    }

    public function testRejectsInvalidContentId(): void
    {
        $this->expectException(InvalidContentIdException::class);

        ($this->handler)(new RunAgentCommand('not-a-valid-uuid', 'What is Rome?'));
    }

    public function testRejectsInvalidConversationId(): void
    {
        $this->expectException(InvalidConversationIdException::class);

        ($this->handler)(new RunAgentCommand(
            self::CONTENT_ID,
            'What is Rome?',
            'not-a-valid-uuid',
        ));
    }

    public function testRejectsEmptyQuestion(): void
    {
        $this->expectException(InvalidAgentPlanException::class);
        $this->expectExceptionMessage('Agent question cannot be empty');

        ($this->handler)(new RunAgentCommand(self::CONTENT_ID, '   '));
    }

    public function testProducesDeterministicExecutionTrace(): void
    {
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
