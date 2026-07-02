<?php

declare(strict_types=1);

namespace App\Application\ShadowMentor;

use App\Domain\ShadowGoals\GoalConstraint;
use App\Domain\ShadowGoals\GoalPortfolio;
use App\Domain\ShadowGoals\LearningGoal;

final class GoalJsonMapper
{
    /** @return array<string, mixed> */
    public function portfolioToArray(GoalPortfolio $portfolio): array
    {
        return [
            'scopeKey' => $portfolio->scopeKey(),
            'goals' => array_map($this->goalToArray(...), $portfolio->goals()->all()),
            'primaryGoal' => null !== $portfolio->primaryGoal()
                ? $this->goalToArray($portfolio->primaryGoal())
                : null,
        ];
    }

    /** @return array<string, mixed> */
    public function goalToArray(LearningGoal $goal): array
    {
        return [
            'id' => $goal->id(),
            'title' => $goal->title(),
            'description' => $goal->description(),
            'motivation' => $goal->motivation(),
            'category' => $goal->category()->value,
            'priority' => $goal->priority()->value,
            'status' => $goal->status()->value,
            'progressPercent' => $goal->progressPercent(),
            'deadline' => $goal->deadline()?->format(DATE_ATOM),
            'targetSkills' => $goal->targetSkills(),
            'requiredKnowledge' => $goal->requiredKnowledge(),
            'successCriteria' => $goal->successCriteria(),
            'constraints' => array_map($this->constraintToArray(...), $goal->constraints()),
        ];
    }

    /** @return array<string, mixed> */
    private function constraintToArray(GoalConstraint $constraint): array
    {
        return [
            'key' => $constraint->key(),
            'label' => $constraint->label(),
            'detail' => $constraint->detail(),
        ];
    }
}
