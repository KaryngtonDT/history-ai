<?php

declare(strict_types=1);

namespace App\Application\ShadowMentor;

use App\Domain\ShadowGoals\GoalPortfolio;
use App\Domain\ShadowGoals\LearningGoal;
use App\Domain\ShadowMentor\GoalImpact;
use App\Domain\ShadowKnowledge\KnowledgeGraph;

final class GoalImpactCalculator
{
    public function __construct(private readonly GoalProgressCalculator $progressCalculator)
    {
    }

    /** @return list<GoalImpact> */
    public function impacts(GoalPortfolio $portfolio, KnowledgeGraph $graph, ?string $conceptKey = null): array
    {
        $impacts = [];

        foreach ($portfolio->goals()->all() as $goal) {
            if ($this->isInactive($goal)) {
                continue;
            }

            $base = $this->progressCalculator->percentForGoal($goal, $graph);
            $impact = $this->impactPercent($goal, $conceptKey, $base);

            $impacts[] = new GoalImpact(
                $goal->id(),
                $goal->title(),
                $impact,
                $this->reason($goal, $conceptKey, $impact),
            );
        }

        return $impacts;
    }

    private function impactPercent(LearningGoal $goal, ?string $conceptKey, int $base): int
    {
        if (null === $conceptKey) {
            return max(1, min(15, 100 - $base));
        }

        $related = in_array($conceptKey, $goal->requiredKnowledge(), true)
            || in_array($conceptKey, $goal->targetSkills(), true);

        return $related ? max(5, min(20, 100 - $base)) : max(1, min(5, (int) round((100 - $base) / 10)));
    }

    private function reason(LearningGoal $goal, ?string $conceptKey, int $impact): string
    {
        if (null === $conceptKey) {
            return sprintf('General progress toward %s.', $goal->title());
        }

        return sprintf('This content can advance %s by about %d%%.', $goal->title(), $impact);
    }

    private function isInactive(LearningGoal $goal): bool
    {
        return \App\Domain\ShadowGoals\GoalStatus::Archived === $goal->status()
            || \App\Domain\ShadowGoals\GoalStatus::Completed === $goal->status();
    }
}
