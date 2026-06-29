<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Agent;

use App\Domain\Agent\AgentStep;
use App\Domain\Agent\AgentTool;
use App\Domain\Agent\Exception\InvalidAgentPlanException;
use PHPUnit\Framework\TestCase;

final class AgentStepTest extends TestCase
{
    public function testExposesOrderToolAndDescription(): void
    {
        $step = new AgentStep(0, AgentTool::SemanticSearch, 'Retrieve relevant chunks');

        self::assertSame(0, $step->order());
        self::assertSame(AgentTool::SemanticSearch, $step->tool());
        self::assertSame('Retrieve relevant chunks', $step->description());
    }

    public function testTrimsDescription(): void
    {
        $step = new AgentStep(1, AgentTool::KnowledgeGraph, '  Explore related artifacts  ');

        self::assertSame('Explore related artifacts', $step->description());
    }

    public function testEqualsComparesOrderToolAndDescription(): void
    {
        $left = new AgentStep(0, AgentTool::SemanticSearch, 'Search');
        $same = new AgentStep(0, AgentTool::SemanticSearch, 'Search');
        $differentOrder = new AgentStep(1, AgentTool::SemanticSearch, 'Search');
        $differentTool = new AgentStep(0, AgentTool::KnowledgeGraph, 'Search');
        $differentDescription = new AgentStep(0, AgentTool::SemanticSearch, 'Graph');

        self::assertTrue($left->equals($same));
        self::assertFalse($left->equals($differentOrder));
        self::assertFalse($left->equals($differentTool));
        self::assertFalse($left->equals($differentDescription));
    }

    public function testRejectsNegativeOrder(): void
    {
        $this->expectException(InvalidAgentPlanException::class);
        $this->expectExceptionMessage('Agent step order must be >= 0');

        new AgentStep(-1, AgentTool::SemanticSearch, 'Search');
    }

    public function testRejectsEmptyDescription(): void
    {
        $this->expectException(InvalidAgentPlanException::class);
        $this->expectExceptionMessage('Agent step description cannot be empty');

        new AgentStep(0, AgentTool::SemanticSearch, '   ');
    }

    public function testIsImmutable(): void
    {
        $step = new AgentStep(0, AgentTool::ConversationMemory, 'Load prior messages');

        self::assertSame(0, $step->order());
        self::assertSame(AgentTool::ConversationMemory, $step->tool());
        self::assertSame('Load prior messages', $step->description());
    }
}
