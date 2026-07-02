<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\ShadowExecutive;

use App\Application\ShadowExecutive\DecisionExplanationBuilder;
use App\Domain\ShadowExecutive\DecisionImpact;
use App\Domain\ShadowExecutive\DecisionType;
use App\Domain\ShadowExecutive\ExecutiveDecision;
use App\Domain\ShadowExecutive\ExecutivePriority;
use App\Domain\ShadowExecutive\ExecutiveReason;
use App\Domain\ShadowGoals\CareerGoal;
use App\Domain\ShadowGoals\GoalPortfolio;
use App\Domain\ShadowKnowledge\KnowledgeGraph;
use App\Domain\ShadowKnowledge\KnowledgeNode;
use App\Domain\ShadowKnowledge\KnowledgeNodeCollection;
use App\Domain\ShadowMentor\MentorPlan;
use PHPUnit\Framework\TestCase;

final class DecisionExplanationBuilderTest extends TestCase
{
    public function testBuildsEvidenceChainWithGoalAndConceptLinks(): void
    {
        $portfolio = GoalPortfolio::create()->addGoal(CareerGoal::create('Senior Backend Developer'));
        $goal = $portfolio->primaryGoal();
        self::assertNotNull($goal);

        $graph = KnowledgeGraph::create()->withNodes(
            KnowledgeNodeCollection::empty()->upsert(
                KnowledgeNode::create('docker', 'Docker'),
            ),
        );

        $decision = ExecutiveDecision::create(
            DecisionType::Review,
            'Review Docker',
            'Docker mastery is stale.',
            ExecutiveReason::create('Docker needs review.', 'Graph signal.'),
            ExecutivePriority::High,
            ['knowledge:mastery:docker'],
            [DecisionImpact::Knowledge],
            $goal->id(),
            'docker',
        );

        $explanation = (new DecisionExplanationBuilder())->build(
            $decision,
            $portfolio,
            MentorPlan::create(),
            $graph,
        );

        self::assertSame('Review Docker', $explanation['title']);
        self::assertSame($goal->id(), $explanation['goalLink']['id']);
        self::assertSame('docker', $explanation['conceptLink']['key']);
        self::assertSame('knowledge', $explanation['evidence'][0]['source']);
    }
}
