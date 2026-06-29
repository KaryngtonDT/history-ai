<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Agent;

use App\Domain\Agent\AgentExecutionStatus;
use App\Domain\Agent\AgentExecutionStep;
use App\Domain\Agent\AgentExecutionStepCollection;
use App\Domain\Agent\AgentTool;
use App\Domain\Agent\Exception\InvalidAgentPlanException;
use PHPUnit\Framework\TestCase;

final class AgentExecutionStepCollectionTest extends TestCase
{
    public function testEmptyCollectionHasNoSteps(): void
    {
        $collection = AgentExecutionStepCollection::empty();

        self::assertTrue($collection->isEmpty());
        self::assertSame(0, $collection->count());
        self::assertSame([], $collection->all());
    }

    public function testConstructorPreservesInsertionOrder(): void
    {
        $steps = [
            new AgentExecutionStep(0, AgentTool::SemanticSearch, AgentExecutionStatus::Completed, 'Semantic search prepared.'),
            new AgentExecutionStep(1, AgentTool::KnowledgeGraph, AgentExecutionStatus::Completed, 'Knowledge graph exploration prepared.'),
            new AgentExecutionStep(2, AgentTool::MultiDocumentChat, AgentExecutionStatus::Completed, 'Multi-document chat prepared.'),
        ];

        $collection = new AgentExecutionStepCollection($steps);

        self::assertSame($steps, $collection->all());
        self::assertSame(3, $collection->count());
    }

    public function testAppendAddsStepWithSequentialOrder(): void
    {
        $collection = AgentExecutionStepCollection::empty()
            ->append(new AgentExecutionStep(0, AgentTool::SemanticSearch, AgentExecutionStatus::Completed, 'Semantic search prepared.'))
            ->append(new AgentExecutionStep(1, AgentTool::MultiDocumentChat, AgentExecutionStatus::Completed, 'Multi-document chat prepared.'));

        self::assertSame(2, $collection->count());
        self::assertSame(
            [AgentTool::SemanticSearch, AgentTool::MultiDocumentChat],
            array_map(
                static fn (AgentExecutionStep $step): AgentTool => $step->tool(),
                $collection->all(),
            ),
        );
    }

    public function testAppendDoesNotMutateOriginalCollection(): void
    {
        $original = AgentExecutionStepCollection::empty()
            ->append(new AgentExecutionStep(0, AgentTool::SemanticSearch, AgentExecutionStatus::Completed, 'Semantic search prepared.'));
        $updated = $original->append(
            new AgentExecutionStep(1, AgentTool::MultiDocumentChat, AgentExecutionStatus::Completed, 'Multi-document chat prepared.'),
        );

        self::assertSame(1, $original->count());
        self::assertSame(2, $updated->count());
    }

    public function testRejectsNonSequentialOrderOnConstruction(): void
    {
        $this->expectException(InvalidAgentPlanException::class);
        $this->expectExceptionMessage('Agent execution steps must be ordered sequentially from 0');

        new AgentExecutionStepCollection([
            new AgentExecutionStep(1, AgentTool::SemanticSearch, AgentExecutionStatus::Completed, 'Semantic search prepared.'),
        ]);
    }

    public function testRejectsOutOfSequenceAppend(): void
    {
        $this->expectException(InvalidAgentPlanException::class);
        $this->expectExceptionMessage('Agent execution step order must be sequential, expected 1, got 2');

        AgentExecutionStepCollection::empty()
            ->append(new AgentExecutionStep(0, AgentTool::SemanticSearch, AgentExecutionStatus::Completed, 'Semantic search prepared.'))
            ->append(new AgentExecutionStep(2, AgentTool::MultiDocumentChat, AgentExecutionStatus::Completed, 'Multi-document chat prepared.'));
    }
}
