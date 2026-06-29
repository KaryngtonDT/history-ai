<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Agent;

use App\Domain\Agent\AgentPlan;
use App\Domain\Agent\AgentTool;
use App\Domain\Agent\Exception\InvalidAgentPlanException;
use PHPUnit\Framework\TestCase;

final class AgentPlanTest extends TestCase
{
    public function testEmptyPlanHasNoSteps(): void
    {
        $plan = AgentPlan::empty();

        self::assertTrue($plan->steps()->isEmpty());
        self::assertSame(0, $plan->toolCount());
        self::assertFalse($plan->containsTool(AgentTool::SemanticSearch));
    }

    public function testAppendBuildsSequentialPlan(): void
    {
        $plan = AgentPlan::empty()
            ->append(AgentTool::SemanticSearch, 'Search Rome and Byzantium')
            ->append(AgentTool::KnowledgeGraph, 'Explore related artifacts')
            ->append(AgentTool::MultiDocumentChat, 'Compare findings');

        self::assertSame(3, $plan->toolCount());
        self::assertSame(
            [0, 1, 2],
            array_map(
                static fn ($step) => $step->order(),
                $plan->steps()->all(),
            ),
        );
        self::assertSame(
            [
                AgentTool::SemanticSearch,
                AgentTool::KnowledgeGraph,
                AgentTool::MultiDocumentChat,
            ],
            array_map(
                static fn ($step) => $step->tool(),
                $plan->steps()->all(),
            ),
        );
    }

    public function testAppendDoesNotMutateOriginalPlan(): void
    {
        $original = AgentPlan::empty()
            ->append(AgentTool::SemanticSearch, 'Search Rome');
        $updated = $original->append(AgentTool::KnowledgeGraph, 'Explore graph');

        self::assertSame(1, $original->toolCount());
        self::assertSame(2, $updated->toolCount());
    }

    public function testContainsToolDetectsPresentTools(): void
    {
        $plan = AgentPlan::empty()
            ->append(AgentTool::SemanticSearch, 'Search Rome')
            ->append(AgentTool::ConversationMemory, 'Load prior messages');

        self::assertTrue($plan->containsTool(AgentTool::SemanticSearch));
        self::assertTrue($plan->containsTool(AgentTool::ConversationMemory));
        self::assertFalse($plan->containsTool(AgentTool::KnowledgeGraph));
    }

    public function testAllowsSameToolWhenNotConsecutive(): void
    {
        $plan = AgentPlan::empty()
            ->append(AgentTool::SemanticSearch, 'Initial search')
            ->append(AgentTool::KnowledgeGraph, 'Explore graph')
            ->append(AgentTool::SemanticSearch, 'Refine search');

        self::assertSame(3, $plan->toolCount());
        self::assertTrue($plan->containsTool(AgentTool::SemanticSearch));
    }

    public function testRejectsDuplicateConsecutiveTool(): void
    {
        $this->expectException(InvalidAgentPlanException::class);
        $this->expectExceptionMessage('Consecutive agent steps cannot use the same tool "semantic_search"');

        AgentPlan::empty()
            ->append(AgentTool::SemanticSearch, 'Initial search')
            ->append(AgentTool::SemanticSearch, 'Duplicate search');
    }

    public function testIsImmutable(): void
    {
        $plan = AgentPlan::empty()
            ->append(AgentTool::SemanticSearch, 'Search Rome');

        self::assertSame(1, $plan->toolCount());
        self::assertSame('Search Rome', $plan->steps()->all()[0]->description());
    }
}
