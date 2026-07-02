<?php

declare(strict_types=1);

namespace App\Infrastructure\ShadowMentor;

use App\Domain\ShadowGoals\GoalMilestone;
use App\Domain\ShadowMentor\Exception\InvalidShadowMentorException;
use App\Domain\ShadowMentor\MentorMission;
use App\Domain\ShadowMentor\MentorMissionCollection;
use App\Domain\ShadowMentor\MentorMissionStatus;
use App\Domain\ShadowMentor\MentorPlan;
use App\Domain\ShadowMentor\MentorPlanId;
use App\Domain\ShadowMentor\RoadmapHorizon;
use App\Domain\ShadowMentor\RoadmapStep;
use App\Domain\ShadowMentor\RoadmapStepCollection;
use App\Domain\ShadowMentor\SkillProgress;
use App\Domain\ShadowMentor\SkillProgressCollection;
use App\Domain\ShadowMentor\GoalMilestoneCollection;
use App\Domain\ShadowMentor\WeeklyReview;
use JsonException;

final class ShadowMentorPersistenceMapper
{
    /** @return array<string, mixed> */
    public function toArray(MentorPlan $plan): array
    {
        return [
            'id' => $plan->id()->value,
            'scopeKey' => $plan->scopeKey(),
            'mentorEnabled' => $plan->mentorEnabled(),
            'currentMissionId' => $plan->currentMissionId(),
            'estimatedCompletionAt' => $plan->estimatedCompletionAt()?->format(DATE_ATOM),
            'missions' => array_map($this->missionToArray(...), $plan->missions()->all()),
            'roadmap' => array_map($this->roadmapToArray(...), $plan->roadmap()->all()),
            'skills' => array_map($this->skillToArray(...), $plan->skills()->all()),
            'milestones' => array_map($this->milestoneToArray(...), $plan->milestones()->all()),
            'weeklyReview' => $this->reviewToArray($plan->weeklyReview()),
        ];
    }

    public function fromJson(string $json): MentorPlan
    {
        try {
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidShadowMentorException('Stored mentor plan is not valid JSON.', 0, $exception);
        }

        if (!is_array($decoded) || !is_string($decoded['id'] ?? null)) {
            throw new InvalidShadowMentorException('Stored mentor plan is invalid.');
        }

        $review = is_array($decoded['weeklyReview'] ?? null) ? $decoded['weeklyReview'] : [];

        return new MentorPlan(
            new MentorPlanId($decoded['id']),
            is_string($decoded['scopeKey'] ?? null) ? $decoded['scopeKey'] : 'default',
            $this->missionsFromArray(is_array($decoded['missions'] ?? null) ? $decoded['missions'] : []),
            $this->roadmapFromArray(is_array($decoded['roadmap'] ?? null) ? $decoded['roadmap'] : []),
            $this->skillsFromArray(is_array($decoded['skills'] ?? null) ? $decoded['skills'] : []),
            $this->milestonesFromArray(is_array($decoded['milestones'] ?? null) ? $decoded['milestones'] : []),
            is_string($decoded['currentMissionId'] ?? null) ? $decoded['currentMissionId'] : null,
            isset($decoded['estimatedCompletionAt']) && is_string($decoded['estimatedCompletionAt'])
                ? new \DateTimeImmutable($decoded['estimatedCompletionAt'])
                : null,
            new WeeklyReview(
                is_string($review['summary'] ?? null) ? $review['summary'] : '',
                (int) ($review['progressDelta'] ?? 0),
                (int) ($review['milestonesCompleted'] ?? 0),
                is_string($review['difficultyNote'] ?? null) ? $review['difficultyNote'] : '',
                is_array($review['recommendations'] ?? null) ? array_values(array_map('strval', $review['recommendations'])) : [],
                (bool) ($review['adaptationPending'] ?? false),
                isset($review['generatedAt']) && is_string($review['generatedAt'])
                    ? new \DateTimeImmutable($review['generatedAt'])
                    : null,
            ),
            (bool) ($decoded['mentorEnabled'] ?? true),
        );
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
            'prerequisiteKeys' => $mission->prerequisiteKeys(),
            'exerciseCount' => $mission->exerciseCount(),
            'validationLabel' => $mission->validationLabel(),
            'unlockedConceptKey' => $mission->unlockedConceptKey(),
            'status' => $mission->status()->value,
            'progressPercent' => $mission->progressPercent(),
        ];
    }

    /** @return array<string, mixed> */
    private function roadmapToArray(RoadmapStep $step): array
    {
        return [
            'horizon' => $step->horizon()->value,
            'label' => $step->label(),
            'detail' => $step->detail(),
            'order' => $step->order(),
        ];
    }

    /** @return array<string, mixed> */
    private function skillToArray(SkillProgress $skill): array
    {
        return [
            'key' => $skill->key(),
            'label' => $skill->label(),
            'percent' => $skill->percent(),
        ];
    }

    /** @return array<string, mixed> */
    private function milestoneToArray(GoalMilestone $milestone): array
    {
        return [
            'id' => $milestone->id(),
            'goalId' => $milestone->goalId(),
            'label' => $milestone->label(),
            'detail' => $milestone->detail(),
            'completed' => $milestone->completed(),
            'targetAt' => $milestone->targetAt()?->format(DATE_ATOM),
            'completedAt' => $milestone->completedAt()?->format(DATE_ATOM),
        ];
    }

    /** @return array<string, mixed> */
    private function reviewToArray(WeeklyReview $review): array
    {
        return [
            'summary' => $review->summary(),
            'progressDelta' => $review->progressDelta(),
            'milestonesCompleted' => $review->milestonesCompleted(),
            'difficultyNote' => $review->difficultyNote(),
            'recommendations' => $review->recommendations(),
            'adaptationPending' => $review->adaptationPending(),
            'generatedAt' => $review->generatedAt()?->format(DATE_ATOM),
        ];
    }

    /** @param list<array<string, mixed>> $items */
    private function missionsFromArray(array $items): MentorMissionCollection
    {
        $missions = [];

        foreach ($items as $item) {
            if (!is_array($item) || !is_string($item['id'] ?? null)) {
                continue;
            }

            $missions[] = new MentorMission(
                $item['id'],
                is_string($item['goalId'] ?? null) ? $item['goalId'] : '',
                is_string($item['title'] ?? null) ? $item['title'] : 'Mission',
                is_string($item['objective'] ?? null) ? $item['objective'] : '',
                (int) ($item['durationMinutes'] ?? 20),
                is_array($item['prerequisiteKeys'] ?? null) ? array_values(array_map('strval', $item['prerequisiteKeys'])) : [],
                (int) ($item['exerciseCount'] ?? 1),
                is_string($item['validationLabel'] ?? null) ? $item['validationLabel'] : 'Complete checkpoint',
                is_string($item['unlockedConceptKey'] ?? null) ? $item['unlockedConceptKey'] : '',
                MentorMissionStatus::tryFrom((string) ($item['status'] ?? 'upcoming')) ?? MentorMissionStatus::Upcoming,
                (int) ($item['progressPercent'] ?? 0),
            );
        }

        return $this->appendMissions(MentorMissionCollection::empty(), $missions);
    }

    /** @param list<MentorMission> $missions */
    private function appendMissions(MentorMissionCollection $collection, array $missions): MentorMissionCollection
    {
        foreach ($missions as $mission) {
            $collection = $collection->upsert($mission);
        }

        return $collection;
    }

    /** @param list<array<string, mixed>> $items */
    private function roadmapFromArray(array $items): RoadmapStepCollection
    {
        $steps = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $steps[] = RoadmapStep::create(
                RoadmapHorizon::tryFrom((string) ($item['horizon'] ?? 'today')) ?? RoadmapHorizon::Today,
                is_string($item['label'] ?? null) ? $item['label'] : '',
                is_string($item['detail'] ?? null) ? $item['detail'] : '',
                (int) ($item['order'] ?? 0),
            );
        }

        return $this->appendRoadmapSteps(RoadmapStepCollection::empty(), $steps);
    }

    /** @param list<array<string, mixed>> $items */
    private function skillsFromArray(array $items): SkillProgressCollection
    {
        $collection = SkillProgressCollection::empty();

        foreach ($items as $item) {
            if (!is_array($item) || !is_string($item['key'] ?? null)) {
                continue;
            }

            $collection = $collection->upsert(SkillProgress::create(
                $item['key'],
                is_string($item['label'] ?? null) ? $item['label'] : $item['key'],
                (int) ($item['percent'] ?? 0),
            ));
        }

        return $collection;
    }

    /** @param list<array<string, mixed>> $items */
    private function milestonesFromArray(array $items): GoalMilestoneCollection
    {
        $collection = GoalMilestoneCollection::empty();

        foreach ($items as $item) {
            if (!is_array($item) || !is_string($item['id'] ?? null)) {
                continue;
            }

            $collection = $collection->append(new GoalMilestone(
                $item['id'],
                is_string($item['goalId'] ?? null) ? $item['goalId'] : '',
                is_string($item['label'] ?? null) ? $item['label'] : '',
                is_string($item['detail'] ?? null) ? $item['detail'] : '',
                (bool) ($item['completed'] ?? false),
                isset($item['targetAt']) && is_string($item['targetAt'])
                    ? new \DateTimeImmutable($item['targetAt'])
                    : null,
                isset($item['completedAt']) && is_string($item['completedAt'])
                    ? new \DateTimeImmutable($item['completedAt'])
                    : null,
            ));
        }

        return $collection;
    }

    /** @param list<RoadmapStep> $steps */
    private function appendRoadmapSteps(RoadmapStepCollection $collection, array $steps): RoadmapStepCollection
    {
        foreach ($steps as $step) {
            $collection = $collection->append($step);
        }

        return $collection;
    }
}
