<?php

declare(strict_types=1);

namespace App\Application\ShadowExecutive;

use App\Domain\ShadowExecutive\ExecutiveAgenda;
use App\Domain\ShadowExecutive\ExecutiveTask;
use App\Domain\ShadowExecutive\ExecutiveTaskCollection;
use App\Domain\ShadowExecutive\ExecutiveTaskType;
use App\Domain\ShadowMentor\MentorMissionStatus;
use App\Domain\ShadowMentor\MentorPlan;
use App\Domain\ShadowTeaching\ExerciseStatus;
use App\Domain\ShadowTeaching\TeachingPlan;

final class ExecutiveAgendaBuilder
{
    /** @param list<array{conceptKey: string, label: string, reason: string}> $staleConcepts */
    public function build(
        MentorPlan $mentorPlan,
        TeachingPlan $teaching,
        array $staleConcepts,
    ): ExecutiveAgenda {
        $today = ExecutiveTaskCollection::empty();
        $order = 0;

        foreach (array_slice($staleConcepts, 0, 2) as $stale) {
            $today = $today->append(ExecutiveTask::create(
                ExecutiveTaskType::Review,
                sprintf('Review %s', $stale['label']),
                $stale['reason'],
                $order++,
            ));
        }

        $currentMission = $mentorPlan->currentMission();

        if (null !== $currentMission) {
            $today = $today->append(ExecutiveTask::create(
                ExecutiveTaskType::Mission,
                $currentMission->title(),
                $currentMission->objective(),
                $order++,
            ));
        }

        if (null !== $teaching->currentObjectiveKey()) {
            $today = $today->append(ExecutiveTask::create(
                ExecutiveTaskType::Watch,
                'Watch focused content',
                sprintf('Continue learning around %s.', $teaching->currentObjectiveKey()),
                $order++,
            ));
        }

        foreach ($teaching->exercises()->all() as $exercise) {
            if (ExerciseStatus::Correct === $exercise->status()) {
                continue;
            }

            $today = $today->append(ExecutiveTask::create(
                ExecutiveTaskType::Exercise,
                'Complete exercise',
                $exercise->question(),
                $order++,
            ));

            break;
        }

        $upcoming = ExecutiveTaskCollection::empty();
        $upcomingOrder = 0;
        $tomorrow = new \DateTimeImmutable('tomorrow');

        foreach ($mentorPlan->missions()->all() as $mission) {
            if (MentorMissionStatus::Active === $mission->status()) {
                continue;
            }

            if (MentorMissionStatus::Completed === $mission->status()) {
                continue;
            }

            $upcoming = $upcoming->append(ExecutiveTask::create(
                ExecutiveTaskType::Mission,
                $mission->title(),
                $mission->objective(),
                $upcomingOrder++,
                $tomorrow->modify(sprintf('+%d days', $upcomingOrder)),
            ));

            if ($upcomingOrder >= 3) {
                break;
            }
        }

        return new ExecutiveAgenda($today, $upcoming);
    }
}
