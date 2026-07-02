<?php

declare(strict_types=1);

namespace App\Domain\ShadowGoals;

final readonly class CareerGoal
{
    public static function create(string $title, string $motivation = ''): LearningGoal
    {
        return LearningGoal::create($title, GoalCategory::Career, GoalPriority::Primary, '', $motivation)
            ->applyUpdate([
                'targetSkills' => ['architecture', 'backend', 'cloud'],
                'requiredKnowledge' => ['php', 'symfony', 'docker'],
                'successCriteria' => ['Ship production features', 'Lead technical decisions'],
            ]);
    }
}
