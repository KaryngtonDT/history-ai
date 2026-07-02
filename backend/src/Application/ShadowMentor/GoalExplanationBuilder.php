<?php

declare(strict_types=1);

namespace App\Application\ShadowMentor;

use App\Domain\ShadowGoals\GoalPortfolio;
use App\Domain\ShadowGoals\LearningGoal;
use App\Domain\ShadowMentor\MentorPlan;

final class GoalExplanationBuilder
{
    /** @return list<string> */
    public function build(GoalPortfolio $portfolio, MentorPlan $plan): array
    {
        $primary = $portfolio->primaryGoal();

        if (null === $primary) {
            return [];
        }

        $lines = [
            sprintf('Primary goal: %s (%d%% progress).', $primary->title(), $primary->progressPercent()),
            sprintf('Motivation: %s', '' !== $primary->motivation() ? $primary->motivation() : 'Not specified yet.'),
        ];

        $mission = $plan->currentMission();

        if (null !== $mission) {
            $lines[] = sprintf('Current mission: %s — %s', $mission->title(), $mission->objective());
        }

        $milestone = $plan->milestones()->next();

        if (null !== $milestone) {
            $lines[] = sprintf('Next milestone: %s.', $milestone->label());
        }

        foreach ($portfolio->goals()->secondary() as $goal) {
            $lines[] = sprintf('Secondary goal: %s.', $goal->title());
        }

        return $lines;
    }
}
