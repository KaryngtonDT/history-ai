<?php

declare(strict_types=1);

namespace App\Application\ShadowExecutive;

use App\Domain\ShadowExecutive\ExecutiveDecision;
use App\Domain\ShadowGoals\GoalPortfolio;
use App\Domain\ShadowKnowledge\KnowledgeGraph;
use App\Domain\ShadowMentor\MentorPlan;

final class DecisionExplanationBuilder
{
    /** @return array<string, mixed> */
    public function build(
        ExecutiveDecision $decision,
        GoalPortfolio $portfolio,
        MentorPlan $mentorPlan,
        KnowledgeGraph $graph,
    ): array {
        $goal = null !== $decision->linkedGoalId()
            ? $portfolio->goals()->find($decision->linkedGoalId())
            : null;

        $concept = null !== $decision->linkedConceptKey()
            ? $graph->nodes()->find($decision->linkedConceptKey())
            : null;

        $mission = null !== $decision->linkedResourceId()
            ? $mentorPlan->missions()->find($decision->linkedResourceId())
            : null;

        return [
            'decisionId' => $decision->id(),
            'type' => $decision->type()->value,
            'title' => $decision->title(),
            'summary' => $decision->summary(),
            'reason' => [
                'summary' => $decision->reason()->summary(),
                'detail' => $decision->reason()->detail(),
            ],
            'evidence' => array_map(
                fn (string $item): array => $this->resolveEvidence($item, $graph, $mentorPlan),
                $decision->evidence(),
            ),
            'impacts' => array_map(static fn ($impact): string => $impact->value, $decision->impacts()),
            'goalLink' => null !== $goal ? [
                'id' => $goal->id(),
                'title' => $goal->title(),
                'progressPercent' => $goal->progressPercent(),
            ] : null,
            'conceptLink' => null !== $concept ? [
                'key' => $concept->key(),
                'label' => $concept->label(),
            ] : null,
            'resourceLink' => null !== $mission ? [
                'id' => $mission->id(),
                'title' => $mission->title(),
            ] : null,
        ];
    }

    /** @return array<string, mixed> */
    private function resolveEvidence(string $item, KnowledgeGraph $graph, MentorPlan $mentorPlan): array
    {
        if (str_starts_with($item, 'knowledge:')) {
            $parts = explode(':', $item, 3);
            $key = $parts[2] ?? $parts[1] ?? '';
            $node = $graph->nodes()->find($key);
            $mastery = $graph->masteries()->find($key);

            return [
                'source' => 'knowledge',
                'reference' => $item,
                'label' => null !== $node ? $node->label() : $key,
                'detail' => null !== $mastery
                    ? sprintf('%d%% mastery, %d exposures', $mastery->percent(), $mastery->exposureCount())
                    : '',
            ];
        }

        if (str_starts_with($item, 'mentor:')) {
            return [
                'source' => 'mentor',
                'reference' => $item,
                'label' => 'Mentor plan signal',
                'detail' => sprintf('%d active missions', count(array_filter(
                    $mentorPlan->missions()->all(),
                    static fn ($mission): bool => \App\Domain\ShadowMentor\MentorMissionStatus::Active === $mission->status(),
                ))),
            ];
        }

        return [
            'source' => explode(':', $item)[0] ?? 'unknown',
            'reference' => $item,
            'label' => $item,
            'detail' => '',
        ];
    }
}
