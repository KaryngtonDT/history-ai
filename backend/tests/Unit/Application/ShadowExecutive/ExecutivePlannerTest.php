<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\ShadowExecutive;

use App\Application\ShadowExecutive\ExecutiveAgendaBuilder;
use App\Application\ShadowExecutive\ExecutiveDecisionBuilder;
use App\Application\ShadowExecutive\ExecutivePlanner;
use App\Application\ShadowExecutive\EnergyAwarePlanner;
use App\Application\ShadowExecutive\LearningOpportunityDetector;
use App\Application\ShadowExecutive\OpportunityEngine;
use App\Application\ShadowExecutive\PriorityResolver;
use App\Application\ShadowExecutive\ResourceRecommendationEngine;
use App\Application\ShadowExecutive\ReviewScheduler;
use App\Application\ShadowTeaching\CheckpointGenerator;
use App\Application\ShadowTeaching\ExercisePlanner;
use App\Application\ShadowTeaching\LearningObjectiveResolver;
use App\Application\ShadowTeaching\LearningPathBuilder;
use App\Application\ShadowTeaching\RevisionPlanner;
use App\Application\ShadowTeaching\TeachingAdvisor;
use App\Application\ShadowTeaching\TeachingBuilder;
use App\Application\ShadowTeaching\TeachingPlanner;
use App\Application\ShadowTeaching\TeachingProgressUpdater;
use App\Application\ShadowMemory\KnowledgeConnectionBuilder;
use App\Application\ShadowMemory\KnowledgeSimilarityResolver;
use App\Application\ShadowMemory\MemoryBuilder;
use App\Application\ShadowMemory\MemoryCollector;
use App\Application\ShadowMemory\MemoryEvolutionEngine;
use App\Infrastructure\ShadowMemory\InMemoryShadowMemoryRepository;
use App\Infrastructure\ShadowRelationship\InMemoryShadowRelationshipRepository;
use App\Infrastructure\ShadowTeaching\InMemoryShadowTeachingRepository;
use App\Domain\ShadowExecutive\ExecutivePlan;
use App\Domain\ShadowGoals\CareerGoal;
use App\Domain\ShadowGoals\GoalPortfolio;
use App\Domain\ShadowKnowledge\KnowledgeConfidence;
use App\Domain\ShadowKnowledge\KnowledgeGraph;
use App\Domain\ShadowKnowledge\KnowledgeMastery;
use App\Domain\ShadowKnowledge\KnowledgeMasteryCollection;
use App\Domain\ShadowKnowledge\KnowledgeNode;
use App\Domain\ShadowKnowledge\KnowledgeNodeCollection;
use App\Domain\ShadowMentor\MentorPlan;
use PHPUnit\Framework\TestCase;

final class ExecutivePlannerTest extends TestCase
{
    public function testPlanBuildsAgendaAndPendingDecisions(): void
    {
        $portfolio = GoalPortfolio::create()->addGoal(CareerGoal::create('Senior Backend Developer'));
        $graph = KnowledgeGraph::create()
            ->withMasteries(
                KnowledgeMasteryCollection::empty()->upsert(
                    new KnowledgeMastery(
                        'docker',
                        35,
                        1,
                        0,
                        0,
                        [],
                        KnowledgeConfidence::Low,
                        null,
                        new \DateTimeImmutable('-30 days'),
                    ),
                ),
            )
            ->withNodes(
                KnowledgeNodeCollection::empty()->upsert(
                    KnowledgeNode::create('docker', 'Docker'),
                ),
            );

        $plan = $this->planner()->plan(
            $portfolio,
            MentorPlan::create(),
            $graph,
            ExecutivePlan::create(),
        );

        self::assertNotEmpty($plan->agenda()->today()->all());
        self::assertNotEmpty($plan->pendingDecisions()->all());
    }

    private function planner(): ExecutivePlanner
    {
        return new ExecutivePlanner(
            new ReviewScheduler(),
            new LearningOpportunityDetector(),
            new ExecutiveDecisionBuilder(new PriorityResolver()),
            new ExecutiveAgendaBuilder(),
            new ResourceRecommendationEngine(),
            new OpportunityEngine(new LearningOpportunityDetector()),
            new EnergyAwarePlanner(),
            $this->teachingBuilder(),
        );
    }

    private function teachingBuilder(): TeachingBuilder
    {
        $memoryBuilder = new MemoryBuilder(
            new InMemoryShadowMemoryRepository(),
            new MemoryCollector(),
            new MemoryEvolutionEngine(
                new KnowledgeSimilarityResolver(),
                new KnowledgeConnectionBuilder(),
            ),
            new InMemoryShadowRelationshipRepository(),
        );

        return new TeachingBuilder(
            new InMemoryShadowTeachingRepository(),
            $memoryBuilder,
            new TeachingPlanner(
                new LearningPathBuilder(new LearningObjectiveResolver()),
                new ExercisePlanner(),
                new RevisionPlanner(),
                new CheckpointGenerator(),
                new TeachingAdvisor(),
            ),
            new TeachingProgressUpdater(),
        );
    }
}
