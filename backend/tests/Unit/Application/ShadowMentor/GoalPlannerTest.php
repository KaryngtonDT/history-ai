<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\ShadowMentor;

use App\Application\ShadowMentor\GoalPlanner;
use App\Application\ShadowMentor\GoalProgressCalculator;
use App\Application\ShadowMentor\LearningMissionBuilder;
use App\Application\ShadowMentor\MilestoneResolver;
use App\Application\ShadowMentor\MultiGoalOrchestrator;
use App\Application\ShadowMentor\RoadmapBuilder;
use App\Domain\ShadowGoals\CareerGoal;
use App\Domain\ShadowGoals\GoalPortfolio;
use App\Domain\ShadowKnowledge\KnowledgeGraph;
use App\Domain\ShadowMentor\MentorMissionStatus;
use App\Domain\ShadowMentor\MentorPlan;
use PHPUnit\Framework\TestCase;

final class GoalPlannerTest extends TestCase
{
    public function testPlanBuildsRoadmapAndActiveMissionForPrimaryGoal(): void
    {
        $portfolio = GoalPortfolio::create()->addGoal(CareerGoal::create('Senior Backend Developer'));
        $planner = new GoalPlanner(
            new RoadmapBuilder(),
            new LearningMissionBuilder(),
            new GoalProgressCalculator(),
            new MilestoneResolver(),
            new MultiGoalOrchestrator(),
        );

        $plan = $planner->plan($portfolio, MentorPlan::create(), KnowledgeGraph::create());

        self::assertNotEmpty($plan->roadmap()->all());
        self::assertNotNull($plan->currentMission());
        self::assertSame(MentorMissionStatus::Active, $plan->currentMission()->status());
        self::assertNotEmpty($plan->milestones()->all());
    }

    public function testPlanReturnsUnchangedWhenMentorDisabled(): void
    {
        $portfolio = GoalPortfolio::create();
        $existing = MentorPlan::create();
        $planner = new GoalPlanner(
            new RoadmapBuilder(),
            new LearningMissionBuilder(),
            new GoalProgressCalculator(),
            new MilestoneResolver(),
            new MultiGoalOrchestrator(),
        );

        $plan = $planner->plan($portfolio, $existing, KnowledgeGraph::create());

        self::assertSame($existing->id()->value, $plan->id()->value);
        self::assertSame([], $plan->roadmap()->all());
    }
}
