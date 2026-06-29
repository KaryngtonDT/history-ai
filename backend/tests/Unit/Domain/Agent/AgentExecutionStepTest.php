<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Agent;

use App\Domain\Agent\AgentExecutionStatus;
use App\Domain\Agent\AgentExecutionStep;
use App\Domain\Agent\AgentTool;
use App\Domain\Agent\Exception\InvalidAgentPlanException;
use PHPUnit\Framework\TestCase;

final class AgentExecutionStepTest extends TestCase
{
    public function testExposesOrderToolStatusAndSummary(): void
    {
        $step = new AgentExecutionStep(
            0,
            AgentTool::SemanticSearch,
            AgentExecutionStatus::Completed,
            'Semantic search prepared.',
        );

        self::assertSame(0, $step->order());
        self::assertSame(AgentTool::SemanticSearch, $step->tool());
        self::assertSame(AgentExecutionStatus::Completed, $step->status());
        self::assertSame('Semantic search prepared.', $step->summary());
    }

    public function testTrimsSummary(): void
    {
        $step = new AgentExecutionStep(
            1,
            AgentTool::KnowledgeGraph,
            AgentExecutionStatus::Completed,
            '  Knowledge graph exploration prepared.  ',
        );

        self::assertSame('Knowledge graph exploration prepared.', $step->summary());
    }

    public function testEqualsComparesAllFields(): void
    {
        $left = new AgentExecutionStep(
            0,
            AgentTool::SemanticSearch,
            AgentExecutionStatus::Completed,
            'Semantic search prepared.',
        );
        $same = new AgentExecutionStep(
            0,
            AgentTool::SemanticSearch,
            AgentExecutionStatus::Completed,
            'Semantic search prepared.',
        );
        $differentStatus = new AgentExecutionStep(
            0,
            AgentTool::SemanticSearch,
            AgentExecutionStatus::Skipped,
            'Semantic search prepared.',
        );

        self::assertTrue($left->equals($same));
        self::assertFalse($left->equals($differentStatus));
    }

    public function testRejectsNegativeOrder(): void
    {
        $this->expectException(InvalidAgentPlanException::class);
        $this->expectExceptionMessage('Agent execution step order must be >= 0');

        new AgentExecutionStep(
            -1,
            AgentTool::SemanticSearch,
            AgentExecutionStatus::Completed,
            'Semantic search prepared.',
        );
    }

    public function testRejectsEmptySummary(): void
    {
        $this->expectException(InvalidAgentPlanException::class);
        $this->expectExceptionMessage('Agent execution step summary cannot be empty');

        new AgentExecutionStep(
            0,
            AgentTool::SemanticSearch,
            AgentExecutionStatus::Completed,
            '   ',
        );
    }

    public function testIsImmutable(): void
    {
        $step = new AgentExecutionStep(
            0,
            AgentTool::MultiDocumentChat,
            AgentExecutionStatus::Completed,
            'Multi-document chat prepared.',
        );

        self::assertSame(AgentTool::MultiDocumentChat, $step->tool());
        self::assertSame(AgentExecutionStatus::Completed, $step->status());
    }
}
