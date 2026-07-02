<?php

declare(strict_types=1);

namespace App\Application\ShadowMentor;

use App\Domain\ShadowGoals\GoalPortfolio;
use App\Domain\ShadowMentor\MentorPlan;

final class MentorAdvisor
{
    /** @return list<string> */
    public function recommend(GoalPortfolio $portfolio, MentorPlan $plan, ?string $question = null): array
    {
        if (!$portfolio->mentorEnabled()) {
            return [];
        }

        $lines = [];
        $mission = $plan->currentMission();

        if (null !== $mission) {
            $lines[] = sprintf(
                'This moment supports mission "%s". Tie your answer to %s.',
                $mission->title(),
                $mission->unlockedConceptKey() ?: 'the current concept',
            );
        }

        if (null !== $question && str_contains(strtolower($question), 'skip')) {
            $lines[] = 'If the viewer wants to skip, confirm whether prerequisites for the current mission are already mastered.';
        }

        if ($plan->weeklyReview()->adaptationPending()) {
            $lines[] = 'A weekly review suggests adapting the plan — mention it and ask for approval before changing goals.';
        }

        return $lines;
    }
}
