<?php

declare(strict_types=1);

namespace App\Application\ShadowMentor;

use App\Domain\ShadowGoals\GoalPortfolio;
use App\Domain\ShadowGoals\LearningGoal;
use App\Domain\ShadowKnowledge\KnowledgeGraph;
use App\Domain\ShadowMentor\MentorMissionCollection;

final class MultiGoalOrchestrator
{
    public function missionsForGoals(
        GoalPortfolio $portfolio,
        KnowledgeGraph $graph,
        LearningMissionBuilder $builder,
    ): MentorMissionCollection {
        $collection = MentorMissionCollection::empty();
        $primary = $portfolio->primaryGoal();

        if (null !== $primary) {
            foreach ($builder->build($primary, $graph)->all() as $mission) {
                $collection = $collection->append($mission);
            }
        }

        foreach ($portfolio->goals()->secondary() as $goal) {
            $secondaryMission = $builder->build($goal, $graph)->current();

            if (null !== $secondaryMission) {
                $collection = $collection->append($secondaryMission);
            }
        }

        return $collection;
    }

    /** @return list<LearningGoal> */
    public function orderedGoals(GoalPortfolio $portfolio): array
    {
        $goals = [];
        $primary = $portfolio->primaryGoal();

        if (null !== $primary) {
            $goals[] = $primary;
        }

        return [...$goals, ...$portfolio->goals()->secondary()];
    }
}
