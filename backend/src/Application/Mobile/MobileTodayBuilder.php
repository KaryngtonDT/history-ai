<?php

declare(strict_types=1);

namespace App\Application\Mobile;

use App\Application\ShadowExecutive\ExecutiveCoordinator;
use App\Application\ShadowMentor\MentorBuilder;
use App\Application\ShadowTeaching\TeachingBuilder;
use App\Domain\ShadowExecutive\ExecutiveTask;
use App\Domain\ShadowMentor\MentorMission;
use App\Domain\ShadowTeaching\RevisionItem;

final class MobileTodayBuilder
{
    public function __construct(
        private readonly ExecutiveCoordinator $executiveCoordinator,
        private readonly MentorBuilder $mentorBuilder,
        private readonly TeachingBuilder $teachingBuilder,
    ) {
    }

    /** @return array<string, mixed> */
    public function build(string $scopeKey = 'default'): array
    {
        $executivePlan = $this->executiveCoordinator->syncPlan($scopeKey);
        $mentorPlan = $this->mentorBuilder->getPlan($scopeKey);
        $teachingPlan = $this->teachingBuilder->syncPlan($scopeKey);

        $missions = array_map($this->missionToArray(...), $mentorPlan->missions()->all());
        $currentMission = $mentorPlan->currentMission();
        $revisions = array_map($this->revisionToArray(...), $teachingPlan->revisions()->all());
        $todayTasks = array_map($this->taskToArray(...), $executivePlan->agenda()->today()->all());

        return [
            'missions' => $missions,
            'missionCount' => count($missions),
            'currentMission' => null !== $currentMission ? $this->missionToArray($currentMission) : null,
            'revisions' => $revisions,
            'revisionCount' => count($revisions),
            'agenda' => [
                'today' => $todayTasks,
                'todayCount' => count($todayTasks),
            ],
            'summary' => sprintf(
                '%d mission(s) · %d revision(s)',
                count($missions),
                count($revisions),
            ),
        ];
    }

    /** @return array<string, mixed> */
    public function missions(string $scopeKey = 'default'): array
    {
        $mentorPlan = $this->mentorBuilder->syncPlan($scopeKey);
        $current = $mentorPlan->currentMission();

        return [
            'missions' => array_map($this->missionToArray(...), $mentorPlan->missions()->all()),
            'currentMission' => null !== $current ? $this->missionToArray($current) : null,
        ];
    }

    /** @return array<string, mixed> */
    public function revisions(string $scopeKey = 'default'): array
    {
        $teachingPlan = $this->teachingBuilder->syncPlan($scopeKey);

        return [
            'revisions' => array_map($this->revisionToArray(...), $teachingPlan->revisions()->all()),
        ];
    }

    /** @return array<string, mixed> */
    private function missionToArray(MentorMission $mission): array
    {
        return [
            'id' => $mission->id(),
            'goalId' => $mission->goalId(),
            'title' => $mission->title(),
            'objective' => $mission->objective(),
            'durationMinutes' => $mission->durationMinutes(),
            'status' => $mission->status()->value,
            'progressPercent' => $mission->progressPercent(),
        ];
    }

    /** @return array<string, mixed> */
    private function revisionToArray(RevisionItem $revision): array
    {
        return [
            'conceptKey' => $revision->conceptKey(),
            'label' => $revision->label(),
            'dueAt' => $revision->dueAt()->format(\DateTimeInterface::ATOM),
            'intervalDays' => $revision->intervalDays(),
            'reason' => $revision->reason(),
        ];
    }

    /** @return array<string, mixed> */
    private function taskToArray(ExecutiveTask $task): array
    {
        return [
            'id' => $task->id(),
            'type' => $task->type()->value,
            'label' => $task->label(),
            'detail' => $task->detail(),
            'order' => $task->order(),
            'scheduledAt' => $task->scheduledAt()?->format(\DateTimeInterface::ATOM),
        ];
    }
}
