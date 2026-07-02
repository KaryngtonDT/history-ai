<?php

declare(strict_types=1);

namespace App\Domain\ShadowGoals;

final readonly class SkillGoal
{
    public static function create(string $skill, GoalPriority $priority = GoalPriority::Secondary): LearningGoal
    {
        return LearningGoal::create(
            ucwords(str_replace('_', ' ', $skill)),
            GoalCategory::Programming,
            $priority,
            sprintf('Master %s through focused practice.', $skill),
        )->applyUpdate([
            'targetSkills' => [$skill],
            'requiredKnowledge' => [$skill],
            'successCriteria' => [sprintf('Explain %s confidently', $skill)],
        ]);
    }
}
