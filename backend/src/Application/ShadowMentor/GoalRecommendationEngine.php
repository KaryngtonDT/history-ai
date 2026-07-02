<?php

declare(strict_types=1);

namespace App\Application\ShadowMentor;

use App\Domain\ShadowGoals\GoalPortfolio;

final class GoalRecommendationEngine
{
    /** @return list<string> */
    public function recommend(GoalPortfolio $portfolio): array
    {
        $primary = $portfolio->primaryGoal();

        if (null === $primary) {
            return ['Set a primary goal to unlock mentor guidance.'];
        }

        $recommendations = [
            sprintf('Stay focused on %s before adding new topics.', $primary->title()),
        ];

        foreach ($portfolio->goals()->secondary() as $goal) {
            $recommendations[] = sprintf('Schedule short sessions for secondary goal: %s.', $goal->title());
        }

        return $recommendations;
    }
}
