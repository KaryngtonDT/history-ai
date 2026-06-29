<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Agent;

use App\Domain\Agent\AgentPlan;
use App\Domain\Agent\AgentPlannerInterface;
use App\Domain\Agent\AgentRequest;
use App\Domain\Agent\AgentTool;
use PHPUnit\Framework\TestCase;

final class AgentPlannerInterfaceTest extends TestCase
{
    public function testPlannerInterfaceDefinesPlanMethod(): void
    {
        $request = new AgentRequest('What is Rome?');
        $expectedPlan = AgentPlan::empty()
            ->append(AgentTool::SemanticSearch, 'Search')
            ->append(AgentTool::MultiDocumentChat, 'Answer');

        $planner = $this->createMock(AgentPlannerInterface::class);
        $planner
            ->expects(self::once())
            ->method('plan')
            ->with($request)
            ->willReturn($expectedPlan);

        self::assertSame($expectedPlan, $planner->plan($request));
    }
}
