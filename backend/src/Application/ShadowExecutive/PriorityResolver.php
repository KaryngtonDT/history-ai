<?php

declare(strict_types=1);

namespace App\Application\ShadowExecutive;

use App\Domain\ShadowExecutive\ExecutivePriority;
use App\Domain\ShadowGoals\GoalPriority;
use App\Domain\ShadowGoals\LearningGoal;
use App\Domain\ShadowKnowledge\KnowledgeGraph;

final class PriorityResolver
{
    private const int STALE_DAYS_THRESHOLD = 28;

    public function resolve(
        ?LearningGoal $primaryGoal,
        KnowledgeGraph $graph,
        ?string $conceptKey = null,
        int $masteryPercent = 0,
        ?\DateTimeImmutable $lastSeenAt = null,
    ): ExecutivePriority {
        if (null !== $primaryGoal && GoalPriority::Primary === $primaryGoal->priority()) {
            $deadline = $primaryGoal->deadline();

            if (null !== $deadline && $deadline <= new \DateTimeImmutable('+14 days')) {
                return ExecutivePriority::Critical;
            }

            if ($primaryGoal->progressPercent() < 30) {
                return ExecutivePriority::High;
            }
        }

        if (null !== $conceptKey && $this->isPrerequisiteGap($graph, $conceptKey, $primaryGoal)) {
            return ExecutivePriority::High;
        }

        if ($masteryPercent < 30) {
            return ExecutivePriority::High;
        }

        if ($this->isStale($lastSeenAt) || $masteryPercent < 50) {
            return ExecutivePriority::Normal;
        }

        return ExecutivePriority::Low;
    }

    private function isPrerequisiteGap(
        KnowledgeGraph $graph,
        string $conceptKey,
        ?LearningGoal $primaryGoal,
    ): bool {
        if (null === $primaryGoal) {
            return false;
        }

        foreach ($primaryGoal->targetSkills() as $skillKey) {
            if ($skillKey === $conceptKey) {
                continue;
            }

            foreach ($graph->edges()->forKey($skillKey) as $edge) {
                if ($edge->toKey() === $conceptKey || $edge->fromKey() === $conceptKey) {
                    $mastery = $graph->masteries()->find($conceptKey);

                    if (null === $mastery || $mastery->percent() < 50) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function isStale(?\DateTimeImmutable $lastSeenAt): bool
    {
        if (null === $lastSeenAt) {
            return false;
        }

        $threshold = new \DateTimeImmutable(sprintf('-%d days', self::STALE_DAYS_THRESHOLD));

        return $lastSeenAt < $threshold;
    }
}
