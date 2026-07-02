<?php

declare(strict_types=1);

namespace App\Application\ShadowExecutive;

use App\Domain\ShadowExecutive\ExecutiveOpportunity;
use App\Domain\ShadowGoals\GoalPortfolio;
use App\Domain\ShadowKnowledge\KnowledgeEdgeType;
use App\Domain\ShadowKnowledge\KnowledgeGraph;
use App\Domain\ShadowTeaching\TeachingPlan;

final class LearningOpportunityDetector
{
    /** @return list<ExecutiveOpportunity> */
    public function detect(
        KnowledgeGraph $graph,
        TeachingPlan $teaching,
        GoalPortfolio $portfolio,
    ): array {
        $opportunities = [
            ...$this->revisionGaps($teaching),
            ...$this->prerequisiteChains($graph, $portfolio),
        ];

        return $opportunities;
    }

    /** @return list<ExecutiveOpportunity> */
    private function revisionGaps(TeachingPlan $teaching): array
    {
        $opportunities = [];

        $now = new \DateTimeImmutable();

        foreach ($teaching->revisions()->all() as $revision) {
            if ($revision->dueAt() > $now) {
                continue;
            }

            $opportunities[] = ExecutiveOpportunity::create(
                sprintf('Revision due: %s', $revision->label()),
                $revision->reason(),
                'teaching',
            );
        }

        return $opportunities;
    }

    /** @return list<ExecutiveOpportunity> */
    private function prerequisiteChains(KnowledgeGraph $graph, GoalPortfolio $portfolio): array
    {
        $primary = $portfolio->primaryGoal();

        if (null === $primary) {
            return [];
        }

        $opportunities = [];

        foreach ($primary->targetSkills() as $skillKey) {
            $skillMastery = $graph->masteries()->find($skillKey);

            if (null !== $skillMastery && $skillMastery->percent() >= 50) {
                continue;
            }

            foreach ($graph->edges()->forKey($skillKey) as $edge) {
                if (!in_array($edge->type(), [KnowledgeEdgeType::Prerequisite, KnowledgeEdgeType::DependsOn], true)) {
                    continue;
                }

                $prerequisiteKey = $edge->fromKey() === $skillKey ? $edge->toKey() : $edge->fromKey();
                $prerequisite = $graph->masteries()->find($prerequisiteKey);

                if (null !== $prerequisite && $prerequisite->percent() >= 60) {
                    continue;
                }

                $label = sprintf('Strengthen %s before %s', $prerequisiteKey, $skillKey);
                $detail = $edge->reason() !== '' ? $edge->reason() : sprintf('%s depends on %s.', $skillKey, $prerequisiteKey);

                $opportunities[] = ExecutiveOpportunity::create($label, $detail, 'knowledge');
            }
        }

        return $opportunities;
    }
}
