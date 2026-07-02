<?php

declare(strict_types=1);

namespace App\Application\ShadowMentor;

use App\Domain\ShadowGoals\GoalMilestone;
use App\Domain\ShadowGoals\GoalPortfolio;
use App\Domain\ShadowKnowledge\KnowledgeGraph;
use App\Domain\ShadowMentor\GoalImpact;
use App\Domain\ShadowMentor\MentorMission;
use App\Domain\ShadowMentor\MentorPlan;
use App\Domain\ShadowMentor\RoadmapStep;
use App\Domain\ShadowMentor\SkillProgress;
use App\Domain\ShadowMentor\WeeklyReview;

final class MentorJsonMapper
{
    public function __construct(
        private readonly GoalJsonMapper $goalMapper,
        private readonly GoalImpactCalculator $impactCalculator,
    ) {
    }

    /** @return array<string, mixed> */
    public function dashboard(
        GoalPortfolio $portfolio,
        MentorPlan $plan,
        KnowledgeGraph $graph,
        ?string $conceptKey = null,
    ): array {
        $primary = $portfolio->primaryGoal();
        $currentMission = $plan->currentMission();

        return [
            'scopeKey' => $portfolio->scopeKey(),
            'primaryGoal' => null !== $primary ? $this->goalMapper->goalToArray($primary) : null,
            'plan' => $this->planToArray($plan),
            'currentMission' => null !== $currentMission ? $this->missionToArray($currentMission) : null,
            'nextMilestone' => null !== $plan->milestones()->next()
                ? $this->milestoneToArray($plan->milestones()->next())
                : null,
            'goalImpact' => array_map($this->impactToArray(...), $this->impactCalculator->impacts($portfolio, $graph, $conceptKey)),
        ];
    }

    /** @return array<string, mixed> */
    public function planToArray(MentorPlan $plan): array
    {
        return [
            'id' => $plan->id()->value,
            'scopeKey' => $plan->scopeKey(),
            'mentorEnabled' => $plan->mentorEnabled(),
            'missions' => array_map($this->missionToArray(...), $plan->missions()->all()),
            'roadmap' => array_map($this->roadmapToArray(...), $plan->roadmap()->all()),
            'skills' => array_map($this->skillToArray(...), $plan->skills()->all()),
            'milestones' => array_map($this->milestoneToArray(...), $plan->milestones()->all()),
            'currentMissionId' => $plan->currentMissionId(),
            'estimatedCompletionAt' => $plan->estimatedCompletionAt()?->format(DATE_ATOM),
            'weeklyReview' => $this->weeklyReviewToArray($plan->weeklyReview()),
        ];
    }

    /** @return array<string, mixed> */
    public function missionsResponse(MentorPlan $plan): array
    {
        $current = $plan->currentMission();

        return [
            'scopeKey' => $plan->scopeKey(),
            'missions' => array_map($this->missionToArray(...), $plan->missions()->all()),
            'currentMission' => null !== $current ? $this->missionToArray($current) : null,
        ];
    }

    /** @return array<string, mixed> */
    public function roadmapResponse(MentorPlan $plan): array
    {
        return [
            'scopeKey' => $plan->scopeKey(),
            'roadmap' => array_map($this->roadmapToArray(...), $plan->roadmap()->all()),
        ];
    }

    /** @return array<string, mixed> */
    public function missionToArray(MentorMission $mission): array
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
    private function weeklyReviewToArray(WeeklyReview $review): array
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

    /** @return array<string, mixed> */
    private function impactToArray(GoalImpact $impact): array
    {
        return [
            'goalId' => $impact->goalId(),
            'goalTitle' => $impact->goalTitle(),
            'impactPercent' => $impact->impactPercent(),
            'reason' => $impact->reason(),
        ];
    }
}
