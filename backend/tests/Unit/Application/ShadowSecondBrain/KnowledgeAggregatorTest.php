<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\ShadowSecondBrain;

use App\Application\ShadowExecutive\ExecutiveAgendaBuilder;
use App\Application\ShadowExecutive\ExecutiveCoordinator;
use App\Application\ShadowExecutive\ExecutiveDecisionBuilder;
use App\Application\ShadowExecutive\ExecutivePlanner;
use App\Application\ShadowExecutive\EnergyAwarePlanner;
use App\Application\ShadowExecutive\LearningOpportunityDetector;
use App\Application\ShadowExecutive\OpportunityEngine;
use App\Application\ShadowExecutive\PriorityResolver;
use App\Application\ShadowExecutive\ResourceRecommendationEngine;
use App\Application\ShadowExecutive\ReviewScheduler;
use App\Application\ShadowKnowledge\KnowledgeBuilder;
use App\Application\ShadowKnowledge\KnowledgeEdgeResolver;
use App\Application\ShadowKnowledge\KnowledgeGraphBuilder;
use App\Application\ShadowMemory\KnowledgeConnectionBuilder;
use App\Application\ShadowMemory\KnowledgeSimilarityResolver;
use App\Application\ShadowMemory\MemoryBuilder;
use App\Application\ShadowMemory\MemoryCollector;
use App\Application\ShadowMemory\MemoryEvolutionEngine;
use App\Application\ShadowMentor\GoalPlanner;
use App\Application\ShadowMentor\GoalProgressCalculator;
use App\Application\ShadowMentor\LearningMissionBuilder;
use App\Application\ShadowMentor\MentorBuilder;
use App\Application\ShadowMentor\MilestoneResolver;
use App\Application\ShadowMentor\MultiGoalOrchestrator;
use App\Application\ShadowMentor\RoadmapBuilder;
use App\Application\ShadowSecondBrain\KnowledgeAggregator;
use App\Application\ShadowTeaching\CheckpointGenerator;
use App\Application\ShadowTeaching\ExercisePlanner;
use App\Application\ShadowTeaching\LearningObjectiveResolver;
use App\Application\ShadowTeaching\LearningPathBuilder;
use App\Application\ShadowTeaching\RevisionPlanner;
use App\Application\ShadowTeaching\TeachingAdvisor;
use App\Application\ShadowTeaching\TeachingBuilder;
use App\Application\ShadowTeaching\TeachingPlanner;
use App\Application\ShadowTeaching\TeachingProgressUpdater;
use App\Domain\ShadowKnowledge\KnowledgeGraph;
use App\Domain\ShadowKnowledge\KnowledgeNode;
use App\Domain\ShadowKnowledge\KnowledgeNodeType;
use App\Infrastructure\ShadowExecutive\InMemoryShadowExecutiveRepository;
use App\Infrastructure\ShadowGoals\InMemoryShadowGoalsRepository;
use App\Infrastructure\ShadowKnowledge\InMemoryShadowKnowledgeRepository;
use App\Infrastructure\ShadowMemory\InMemoryShadowMemoryRepository;
use App\Infrastructure\ShadowMentor\InMemoryShadowMentorRepository;
use App\Infrastructure\ShadowRelationship\InMemoryShadowRelationshipRepository;
use App\Infrastructure\ShadowTeaching\InMemoryShadowTeachingRepository;
use PHPUnit\Framework\TestCase;

final class KnowledgeAggregatorTest extends TestCase
{
    public function testAggregateBuildsEntriesFromGraphNodes(): void
    {
        $knowledgeRepository = new InMemoryShadowKnowledgeRepository();
        $knowledgeRepository->save(
            KnowledgeGraph::create()
                ->upsertNode(KnowledgeNode::create('docker', 'Docker', KnowledgeNodeType::Technology, 'Container runtime'))
                ->upsertNode(KnowledgeNode::create('kubernetes', 'Kubernetes', KnowledgeNodeType::Technology, 'Orchestrator')),
        );

        $memoryBuilder = $this->memoryBuilder();
        $teachingBuilder = $this->teachingBuilder($memoryBuilder);
        $knowledgeBuilder = $this->knowledgeBuilder($knowledgeRepository, $memoryBuilder, $teachingBuilder);
        $mentorBuilder = $this->mentorBuilder($knowledgeBuilder);
        $executiveCoordinator = $this->executiveCoordinator($mentorBuilder, $knowledgeBuilder, $teachingBuilder);

        $aggregator = new KnowledgeAggregator(
            $knowledgeBuilder,
            $memoryBuilder,
            $mentorBuilder,
            $executiveCoordinator,
            $teachingBuilder,
        );

        $result = $aggregator->aggregate('default');

        self::assertGreaterThanOrEqual(2, count($result['entries']->all()));
        self::assertSame('docker', $result['entries']->findByKey('docker')?->conceptKey());
        self::assertSame('Kubernetes', $result['entries']->findByKey('kubernetes')?->label());
    }

    private function memoryBuilder(): MemoryBuilder
    {
        return new MemoryBuilder(
            new InMemoryShadowMemoryRepository(),
            new MemoryCollector(),
            new MemoryEvolutionEngine(
                new KnowledgeSimilarityResolver(),
                new KnowledgeConnectionBuilder(),
            ),
            new InMemoryShadowRelationshipRepository(),
        );
    }

    private function teachingBuilder(MemoryBuilder $memoryBuilder): TeachingBuilder
    {
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

    private function knowledgeBuilder(
        InMemoryShadowKnowledgeRepository $knowledgeRepository,
        MemoryBuilder $memoryBuilder,
        TeachingBuilder $teachingBuilder,
    ): KnowledgeBuilder {
        return new KnowledgeBuilder(
            $knowledgeRepository,
            $memoryBuilder,
            $teachingBuilder,
            new KnowledgeGraphBuilder(new KnowledgeEdgeResolver()),
        );
    }

    private function mentorBuilder(KnowledgeBuilder $knowledgeBuilder): MentorBuilder
    {
        return new MentorBuilder(
            new InMemoryShadowGoalsRepository(),
            new InMemoryShadowMentorRepository(),
            new GoalPlanner(
                new RoadmapBuilder(),
                new LearningMissionBuilder(),
                new GoalProgressCalculator(),
                new MilestoneResolver(),
                new MultiGoalOrchestrator(),
            ),
            $knowledgeBuilder,
            new GoalProgressCalculator(),
        );
    }

    private function executiveCoordinator(
        MentorBuilder $mentorBuilder,
        KnowledgeBuilder $knowledgeBuilder,
        TeachingBuilder $teachingBuilder,
    ): ExecutiveCoordinator {
        return new ExecutiveCoordinator(
            $mentorBuilder,
            new InMemoryShadowExecutiveRepository(),
            new ExecutivePlanner(
                new ReviewScheduler(),
                new LearningOpportunityDetector(),
                new ExecutiveDecisionBuilder(new PriorityResolver()),
                new ExecutiveAgendaBuilder(),
                new ResourceRecommendationEngine(),
                new OpportunityEngine(new LearningOpportunityDetector()),
                new EnergyAwarePlanner(),
                $teachingBuilder,
            ),
            $knowledgeBuilder,
        );
    }
}
