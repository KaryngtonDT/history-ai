<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\ShadowTeaching;

use App\Application\ShadowTeaching\CheckpointGenerator;
use App\Application\ShadowTeaching\ExercisePlanner;
use App\Application\ShadowTeaching\LearningObjectiveResolver;
use App\Application\ShadowTeaching\LearningPathBuilder;
use App\Application\ShadowTeaching\RevisionPlanner;
use App\Application\ShadowTeaching\TeachingAdvisor;
use App\Application\ShadowTeaching\TeachingPlanner;
use App\Domain\ShadowMemory\KnowledgeItem;
use App\Domain\ShadowMemory\MemoryCategory;
use App\Domain\ShadowMemory\MemoryTimeline;
use App\Domain\ShadowTeaching\TeachingPlan;
use PHPUnit\Framework\TestCase;

final class TeachingPlannerTest extends TestCase
{
    public function testPlanBuildsObjectivesExercisesAndMissions(): void
    {
        $planner = new TeachingPlanner(
            new LearningPathBuilder(new LearningObjectiveResolver()),
            new ExercisePlanner(),
            new RevisionPlanner(),
            new CheckpointGenerator(),
            new TeachingAdvisor(),
        );

        $memory = MemoryTimeline::create(scopeKey: 'default')
            ->upsertKnowledge(KnowledgeItem::start(
                'dependency_injection',
                'Dependency Injection',
                MemoryCategory::Concept,
                'Decouple object creation from business logic.',
            ));

        $planned = $planner->plan(TeachingPlan::create(scopeKey: 'default'), $memory);

        self::assertNotNull($planned->currentObjectiveKey());
        self::assertNotEmpty($planned->objectives()->all());
        self::assertNotEmpty($planned->exercises()->all());
        self::assertNotEmpty($planned->checkpoints()->all());
        self::assertNotEmpty($planned->missions()->all());
    }
}
