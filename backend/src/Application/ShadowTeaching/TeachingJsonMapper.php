<?php

declare(strict_types=1);

namespace App\Application\ShadowTeaching;

use App\Domain\ShadowTeaching\LearningCheckpoint;
use App\Domain\ShadowTeaching\LearningObjective;
use App\Domain\ShadowTeaching\RevisionItem;
use App\Domain\ShadowTeaching\TeachingExercise;
use App\Domain\ShadowTeaching\TeachingMission;
use App\Domain\ShadowTeaching\TeachingPlan;
use App\Domain\ShadowTeaching\TeachingSessionRecord;

final class TeachingJsonMapper
{
    /** @return array<string, mixed> */
    public function toArray(TeachingPlan $plan): array
    {
        return [
            'id' => $plan->id()->value,
            'scopeKey' => $plan->scopeKey(),
            'preferences' => [
                'teachingEnabled' => $plan->preferences()->teachingEnabled(),
                'voiceMode' => $plan->preferences()->voiceMode()->value,
                'difficulty' => $plan->preferences()->difficulty()->value,
                'revisionEnabled' => $plan->preferences()->revisionEnabled(),
            ],
            'path' => [
                'title' => $plan->path()->title(),
                'goal' => $plan->path()->goal(),
                'modules' => array_map(
                    static fn ($module) => [
                        'key' => $module->key(),
                        'title' => $module->title(),
                        'objectives' => array_map(
                            static fn (LearningObjective $objective) => [
                                'key' => $objective->key(),
                                'title' => $objective->title(),
                                'description' => $objective->description(),
                                'concepts' => $objective->concepts(),
                                'prerequisites' => $objective->prerequisites(),
                                'status' => $objective->status()->value,
                                'progressPercent' => $objective->progressPercent(),
                                'explanation' => $objective->explanation(),
                            ],
                            $module->objectives(),
                        ),
                    ],
                    $plan->path()->modules(),
                ),
            ],
            'objectives' => array_map($this->objectiveToArray(...), $plan->objectives()->all()),
            'currentObjectiveKey' => $plan->currentObjectiveKey(),
            'currentLesson' => null !== $plan->currentObjective()
                ? $this->objectiveToArray($plan->currentObjective())
                : null,
            'exercises' => array_map($this->exerciseToArray(...), $plan->exercises()->all()),
            'revisions' => array_map($this->revisionToArray(...), $plan->revisions()->all()),
            'checkpoints' => array_map($this->checkpointToArray(...), $plan->checkpoints()->all()),
            'missions' => array_map($this->missionToArray(...), $plan->missions()->all()),
            'history' => array_map($this->historyToArray(...), $plan->history()->all()),
        ];
    }

    /** @return array<string, mixed> */
    public function current(TeachingPlan $plan, TeachingAdvisor $advisor): array
    {
        $current = $plan->currentObjective();
        $recommendation = $advisor->recommend($current, $this->nextObjective($plan));

        return [
            'scopeKey' => $plan->scopeKey(),
            'currentLesson' => null !== $current ? $this->objectiveToArray($current) : null,
            'recommendation' => [
                'message' => $recommendation->message(),
                'action' => $recommendation->action(),
                'objectiveKey' => $recommendation->objectiveKey(),
            ],
            'nextCheckpoint' => $this->nextCheckpoint($plan),
            'pendingExercises' => count(array_filter(
                $plan->exercises()->all(),
                static fn (TeachingExercise $exercise) => 'pending' === $exercise->status()->value,
            )),
            'revisionReminder' => $this->revisionReminder($plan),
        ];
    }

    /** @return array<string, mixed> */
    private function objectiveToArray(LearningObjective $objective): array
    {
        return [
            'key' => $objective->key(),
            'title' => $objective->title(),
            'description' => $objective->description(),
            'concepts' => $objective->concepts(),
            'prerequisites' => $objective->prerequisites(),
            'status' => $objective->status()->value,
            'progressPercent' => $objective->progressPercent(),
            'explanation' => $objective->explanation(),
        ];
    }

    /** @return array<string, mixed> */
    private function exerciseToArray(TeachingExercise $exercise): array
    {
        return [
            'id' => $exercise->id(),
            'type' => $exercise->type()->value,
            'question' => $exercise->question(),
            'options' => $exercise->options(),
            'status' => $exercise->status()->value,
            'objectiveKey' => $exercise->objectiveKey(),
            'explanation' => $exercise->explanation(),
        ];
    }

    /** @return array<string, mixed> */
    private function revisionToArray(RevisionItem $item): array
    {
        return [
            'conceptKey' => $item->conceptKey(),
            'label' => $item->label(),
            'dueAt' => $item->dueAt()->format(DATE_ATOM),
            'intervalDays' => $item->intervalDays(),
            'reason' => $item->reason(),
        ];
    }

    /** @return array<string, mixed> */
    private function checkpointToArray(LearningCheckpoint $checkpoint): array
    {
        return [
            'id' => $checkpoint->id(),
            'objectiveKey' => $checkpoint->objectiveKey(),
            'label' => $checkpoint->label(),
            'completed' => $checkpoint->completed(),
            'completedAt' => $checkpoint->completedAt()?->format(DATE_ATOM),
        ];
    }

    /** @return array<string, mixed> */
    private function missionToArray(TeachingMission $mission): array
    {
        return [
            'number' => $mission->number(),
            'title' => $mission->title(),
            'objectiveKey' => $mission->objectiveKey(),
            'durationMinutes' => $mission->durationMinutes(),
            'exerciseCount' => $mission->exerciseCount(),
            'checkpointCount' => $mission->checkpointCount(),
            'rewardLabel' => $mission->rewardLabel(),
            'status' => $mission->status()->value,
        ];
    }

    /** @return array<string, mixed> */
    private function historyToArray(TeachingSessionRecord $record): array
    {
        return [
            'id' => $record->id(),
            'label' => $record->label(),
            'detail' => $record->detail(),
            'recordedAt' => $record->recordedAt()->format(DATE_ATOM),
        ];
    }

    private function nextCheckpoint(TeachingPlan $plan): ?array
    {
        foreach ($plan->checkpoints()->all() as $checkpoint) {
            if (!$checkpoint->completed()) {
                return $this->checkpointToArray($checkpoint);
            }
        }

        return null;
    }

    private function revisionReminder(TeachingPlan $plan): ?string
    {
        foreach ($plan->revisions()->all() as $item) {
            if ($item->dueAt() <= new \DateTimeImmutable('today 23:59:59')) {
                return sprintf('Review %s today.', $item->label());
            }
        }

        return null;
    }

    private function nextObjective(TeachingPlan $plan): ?LearningObjective
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
