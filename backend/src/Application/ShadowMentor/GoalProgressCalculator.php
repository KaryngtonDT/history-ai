<?php

declare(strict_types=1);

namespace App\Application\ShadowMentor;

use App\Domain\ShadowGoals\LearningGoal;
use App\Domain\ShadowKnowledge\KnowledgeGraph;

final class GoalProgressCalculator
{
    public function percentForGoal(LearningGoal $goal, KnowledgeGraph $graph): int
    {
        $keys = [] !== $goal->requiredKnowledge() ? $goal->requiredKnowledge() : $goal->targetSkills();

        if ([] === $keys) {
            return $goal->progressPercent();
        }

        $total = 0;

        foreach ($keys as $key) {
            $total += $graph->masteries()->find($key)?->percent() ?? 0;
        }

        return (int) round($total / count($keys));
    }
}
