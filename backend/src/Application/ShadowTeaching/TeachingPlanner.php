<?php

declare(strict_types=1);

namespace App\Application\ShadowTeaching;

use App\Domain\ShadowMemory\MemoryTimeline;
use App\Domain\ShadowTeaching\LearningObjective;
use App\Domain\ShadowTeaching\LearningObjectiveCollection;
use App\Domain\ShadowTeaching\TeachingMission;
use App\Domain\ShadowTeaching\TeachingMissionCollection;
use App\Domain\ShadowTeaching\TeachingPlan;
use App\Domain\ShadowTeaching\TeachingProgressStatus;

final class TeachingPlanner
{
    public function __construct(
        private readonly LearningPathBuilder $pathBuilder,
        private readonly ExercisePlanner $exercisePlanner,
        private readonly RevisionPlanner $revisionPlanner,
        private readonly CheckpointGenerator $checkpointGenerator,
        private readonly TeachingAdvisor $advisor,
    ) {
    }

    public function plan(TeachingPlan $plan, MemoryTimeline $memory): TeachingPlan
    {
        $path = $this->pathBuilder->build($memory);
        $objectives = $this->pathBuilder->objectives($memory);
        $current = $this->resolveCurrent($objectives);
        $next = $this->resolveNext($objectives, $current);
        $exercises = $plan->exercises();

        if (null !== $current) {
            foreach ($this->exercisePlanner->forObjective($current)->all() as $exercise) {
                if (null === $exercises->find($exercise->id())) {
                    $exercises = $exercises->upsert($exercise);
                }
            }
        }

        $checkpoints = $plan->checkpoints();

        if (null !== $current) {
            $checkpoints = $this->checkpointGenerator->merge($checkpoints, $current);
        }

        return $plan
            ->withPath($path)
            ->withObjectives($objectives)
            ->withExercises($exercises)
            ->withRevisions($this->revisionPlanner->build($memory))
            ->withCheckpoints($checkpoints)
            ->withMissions($this->buildMissions($objectives, $current))
            ->withCurrentObjectiveKey($current?->key());
    }

    public function recommendation(TeachingPlan $plan): \App\Domain\ShadowTeaching\TeachingRecommendation
    {
        $objectives = $plan->objectives()->all();
        $current = $plan->currentObjective();
        $next = $this->resolveNext($plan->objectives(), $current);

        return $this->advisor->recommend($current, $next);
    }

    private function resolveCurrent(LearningObjectiveCollection $objectives): ?LearningObjective
    {
        foreach ($objectives->all() as $objective) {
            if (TeachingProgressStatus::Mastered !== $objective->status()) {
                return $objective;
            }
        }

        return $objectives->all()[0] ?? null;
    }

    private function resolveNext(
        LearningObjectiveCollection $objectives,
        ?LearningObjective $current,
    ): ?LearningObjective {
        $foundCurrent = null === $current;

        foreach ($objectives->all() as $objective) {
            if (!$foundCurrent) {
                if ($objective->key() === $current->key()) {
                    $foundCurrent = true;
                }

                continue;
            }

            if (TeachingProgressStatus::Mastered !== $objective->status()) {
                return $objective;
            }
        }

        return null;
    }

    private function buildMissions(
        LearningObjectiveCollection $objectives,
        ?LearningObjective $current,
    ): TeachingMissionCollection {
        $missions = [];
        $number = 1;

        foreach ($objectives->all() as $objective) {
            $missions[] = new TeachingMission(
                $number,
                sprintf('Understand %s', $objective->title()),
                $objective->key(),
                20,
                3,
                1,
                sprintf('Concept unlocked: %s', $objective->title()),
                $objective->key() === ($current?->key()) ? TeachingProgressStatus::Learning : $objective->status(),
            );
            ++$number;

            if ($number > 12) {
                break;
            }
        }

        return new TeachingMissionCollection($missions);
    }
}
