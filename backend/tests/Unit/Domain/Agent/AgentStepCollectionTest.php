<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Agent;

use App\Domain\Agent\AgentStep;
use App\Domain\Agent\AgentStepCollection;
use App\Domain\Agent\AgentTool;
use App\Domain\Agent\Exception\InvalidAgentPlanException;
use PHPUnit\Framework\TestCase;

final class AgentStepCollectionTest extends TestCase
{
    public function testEmptyCollectionHasNoSteps(): void
    {
        $collection = AgentStepCollection::empty();

        self::assertTrue($collection->isEmpty());
        self::assertSame(0, $collection->count());
        self::assertSame([], $collection->all());
    }

    public function testConstructorPreservesInsertionOrder(): void
    {
        $steps = [
            new AgentStep(0, AgentTool::SemanticSearch, 'Search Rome'),
            new AgentStep(1, AgentTool::KnowledgeGraph, 'Explore graph'),
            new AgentStep(2, AgentTool::MultiDocumentChat, 'Synthesize answer'),
        ];

        $collection = new AgentStepCollection($steps);

        self::assertSame($steps, $collection->all());
        self::assertSame(3, $collection->count());
    }

    public function testAppendAddsStepWithSequentialOrder(): void
    {
        $collection = AgentStepCollection::empty()
            ->append(new AgentStep(0, AgentTool::SemanticSearch, 'Search Rome'))
            ->append(new AgentStep(1, AgentTool::KnowledgeGraph, 'Explore graph'));

        self::assertSame(2, $collection->count());
        self::assertSame(
            [AgentTool::SemanticSearch, AgentTool::KnowledgeGraph],
            array_map(
                static fn (AgentStep $step): AgentTool => $step->tool(),
                $collection->all(),
            ),
        );
    }

    public function testAppendDoesNotMutateOriginalCollection(): void
    {
        $original = AgentStepCollection::empty()
            ->append(new AgentStep(0, AgentTool::SemanticSearch, 'Search Rome'));
        $updated = $original->append(new AgentStep(1, AgentTool::KnowledgeGraph, 'Explore graph'));

        self::assertSame(1, $original->count());
        self::assertSame(2, $updated->count());
    }

    public function testRejectsNonSequentialOrderOnConstruction(): void
    {
        $this->expectException(InvalidAgentPlanException::class);
        $this->expectExceptionMessage('Agent steps must be ordered sequentially from 0');

        new AgentStepCollection([
            new AgentStep(1, AgentTool::SemanticSearch, 'Search Rome'),
        ]);
    }

    public function testRejectsOutOfSequenceAppend(): void
    {
        $this->expectException(InvalidAgentPlanException::class);
        $this->expectExceptionMessage('Agent step order must be sequential, expected 1, got 2');

        AgentStepCollection::empty()
            ->append(new AgentStep(0, AgentTool::SemanticSearch, 'Search Rome'))
            ->append(new AgentStep(2, AgentTool::KnowledgeGraph, 'Explore graph'));
    }
}
