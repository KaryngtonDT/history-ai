<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Agent;

use App\Domain\Agent\AgentExecutionResult;
use App\Domain\Agent\AgentExecutionStatus;
use App\Domain\Agent\AgentExecutionStep;
use App\Domain\Agent\AgentExecutionStepCollection;
use App\Domain\Agent\AgentPlan;
use App\Domain\Agent\AgentTool;
use App\Domain\Agent\Exception\InvalidAgentPlanException;
use PHPUnit\Framework\TestCase;

final class AgentExecutionResultTest extends TestCase
{
    public function testExposesPlanStepsAndFinalSummary(): void
    {
        $plan = AgentPlan::empty()
            ->append(AgentTool::SemanticSearch, 'Search Rome')
            ->append(AgentTool::MultiDocumentChat, 'Answer');
        $steps = new AgentExecutionStepCollection([
            new AgentExecutionStep(0, AgentTool::SemanticSearch, AgentExecutionStatus::Completed, 'Semantic search prepared.'),
            new AgentExecutionStep(1, AgentTool::MultiDocumentChat, AgentExecutionStatus::Completed, 'Multi-document chat prepared.'),
        ]);

        $result = new AgentExecutionResult($plan, $steps, 'Agent workflow completed.');

        self::assertSame($plan, $result->plan());
        self::assertSame($steps, $result->steps());
        self::assertSame('Agent workflow completed.', $result->finalSummary());
    }

    public function testTrimsFinalSummary(): void
    {
        $plan = AgentPlan::empty()->append(AgentTool::SemanticSearch, 'Search');
        $steps = AgentExecutionStepCollection::empty()
            ->append(new AgentExecutionStep(0, AgentTool::SemanticSearch, AgentExecutionStatus::Completed, 'Semantic search prepared.'));

        $result = new AgentExecutionResult($plan, $steps, '  Agent workflow completed.  ');

        self::assertSame('Agent workflow completed.', $result->finalSummary());
    }

    public function testRejectsEmptyFinalSummary(): void
    {
        $plan = AgentPlan::empty()->append(AgentTool::SemanticSearch, 'Search');
        $steps = AgentExecutionStepCollection::empty()
            ->append(new AgentExecutionStep(0, AgentTool::SemanticSearch, AgentExecutionStatus::Completed, 'Semantic search prepared.'));

        $this->expectException(InvalidAgentPlanException::class);
        $this->expectExceptionMessage('Agent execution final summary cannot be empty');

        new AgentExecutionResult($plan, $steps, '   ');
    }

    public function testIsImmutable(): void
    {
        $plan = AgentPlan::empty()->append(AgentTool::SemanticSearch, 'Search');
        $steps = AgentExecutionStepCollection::empty()
            ->append(new AgentExecutionStep(0, AgentTool::SemanticSearch, AgentExecutionStatus::Completed, 'Semantic search prepared.'));
        $result = new AgentExecutionResult($plan, $steps, 'Agent workflow completed.');

        self::assertSame(1, $result->plan()->toolCount());
        self::assertSame(1, $result->steps()->count());
    }
}
