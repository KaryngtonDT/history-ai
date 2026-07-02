<?php

declare(strict_types=1);

namespace App\Application\ShadowMentor;

use App\Domain\ShadowGoals\GoalMilestone;
use App\Domain\ShadowGoals\LearningGoal;
use App\Domain\ShadowMentor\GoalMilestoneCollection;

final class MilestoneResolver
{
    public function resolve(LearningGoal $goal): GoalMilestoneCollection
    {
        $collection = GoalMilestoneCollection::empty();
        $order = 0;

        foreach ($goal->targetSkills() as $skill) {
            $collection = $collection->append(GoalMilestone::create(
                $goal->id(),
                ucwords(str_replace('_', ' ', $skill)),
                sprintf('Demonstrate %s in a practical exercise.', $skill),
                (new \DateTimeImmutable())->modify(sprintf('+%d months', ++$order * 2)),
            ));
        }

        if ([] === $goal->targetSkills()) {
            $collection = $collection->append(GoalMilestone::create(
                $goal->id(),
                'First milestone',
                'Complete your first mentor mission.',
            ));
        }

        return $collection;
    }
}
