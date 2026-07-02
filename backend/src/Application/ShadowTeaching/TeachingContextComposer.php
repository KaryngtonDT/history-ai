<?php

declare(strict_types=1);

namespace App\Application\ShadowTeaching;

use App\Domain\ShadowTeaching\ExerciseStatus;
use App\Domain\ShadowTeaching\ShadowTeachingRepositoryInterface;

final class TeachingContextComposer
{
    public function __construct(
        private readonly ShadowTeachingRepositoryInterface $repository,
        private readonly TeachingBuilder $builder,
        private readonly TeachingAdvisor $advisor,
        private readonly TeachingExplanationBuilder $explanationBuilder,
    ) {
    }

    /** @return list<string> */
    public function promptLines(string $scopeKey = 'default'): array
    {
        $plan = $this->builder->syncPlan($scopeKey);

        if (!$plan->preferences()->teachingEnabled()) {
            return [];
        }

        $lines = [];
        $recommendation = $this->advisor->recommend(
            $plan->currentObjective(),
            $this->nextObjective($plan),
        );

        $lines[] = 'Teaching plan: '.$recommendation->message();

        if (null !== $plan->currentObjective()) {
            $current = $plan->currentObjective();
            $lines[] = sprintf(
                'Current lesson: %s (%d%% progress, status=%s).',
                $current->title(),
                $current->progressPercent(),
                $current->status()->value,
            );
        }

        $pendingExercises = array_filter(
            $plan->exercises()->all(),
            static fn ($exercise) => ExerciseStatus::Pending === $exercise->status(),
        );

        if ([] !== $pendingExercises) {
            $lines[] = sprintf(
                'Before continuing, consider %d pending exercise(s) on the current lesson.',
                count($pendingExercises),
            );
        }

        $dueToday = array_filter(
            $plan->revisions()->all(),
            static fn ($item) => $item->dueAt() <= new \DateTimeImmutable('today 23:59:59'),
        );

        if ([] !== $dueToday && $plan->preferences()->revisionEnabled()) {
            $lines[] = 'Revision reminder: '.count($dueToday).' concept(s) should be reviewed today.';
        }

        $lines = [...$lines, ...$this->explanationBuilder->voiceLines($plan->preferences()->voiceMode())];
        $lines[] = 'Lead the learner through the plan naturally; keep teaching explainable and reversible.';

        return $lines;
    }

    private function nextObjective(\App\Domain\ShadowTeaching\TeachingPlan $plan): ?\App\Domain\ShadowTeaching\LearningObjective
    {
        $current = $plan->currentObjectiveKey();
        $found = null === $current;

        foreach ($plan->objectives()->all() as $objective) {
            if (!$found) {
                if ($objective->key() === $current) {
                    $found = true;
                }

                continue;
            }

            return $objective;
        }

        return null;
    }
}
