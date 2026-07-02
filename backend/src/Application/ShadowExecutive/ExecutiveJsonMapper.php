<?php

declare(strict_types=1);

namespace App\Application\ShadowExecutive;

use App\Domain\ShadowExecutive\DecisionStatus;
use App\Domain\ShadowExecutive\ExecutiveDecision;
use App\Domain\ShadowExecutive\ExecutivePlan;
use App\Domain\ShadowExecutive\ExecutiveTask;
use App\Domain\ShadowExecutive\ExecutiveRecommendation;
use App\Domain\ShadowGoals\GoalPortfolio;
use App\Domain\ShadowKnowledge\KnowledgeGraph;
use App\Domain\ShadowMentor\MentorPlan;

final class ExecutiveJsonMapper
{
    public function __construct(
        private readonly DecisionExplanationBuilder $explanationBuilder,
    ) {
    }

    /** @return array<string, mixed> */
    public function dashboard(
        ExecutivePlan $plan,
        GoalPortfolio $portfolio,
        MentorPlan $mentorPlan,
        KnowledgeGraph $graph,
    ): array {
        return [
            'scopeKey' => $plan->scopeKey(),
            'executiveEnabled' => $plan->executiveEnabled(),
            'availableMinutes' => $plan->availableMinutes(),
            'agenda' => $this->agenda($plan),
            'pendingDecisions' => array_map(
                fn (ExecutiveDecision $decision): array => $this->decisionToArray($decision, $portfolio, $mentorPlan, $graph),
                $plan->pendingDecisions()->all(),
            ),
            'recommendations' => array_map(
                $this->recommendationToArray(...),
                $plan->recommendations()->all(),
            ),
            'weeklyReview' => $this->weeklyReviewToArray($plan),
            'historySummary' => $this->historySummary($plan),
        ];
    }

    /** @return array<string, mixed> */
    public function agenda(ExecutivePlan $plan): array
    {
        return [
            'scopeKey' => $plan->scopeKey(),
            'availableMinutes' => $plan->availableMinutes(),
            'today' => array_map($this->taskToArray(...), $plan->agenda()->today()->all()),
            'upcoming' => array_map($this->taskToArray(...), $plan->agenda()->upcoming()->all()),
        ];
    }

    /** @return array<string, mixed> */
    public function recommendations(ExecutivePlan $plan): array
    {
        return [
            'scopeKey' => $plan->scopeKey(),
            'recommendations' => array_map(
                $this->recommendationToArray(...),
                $plan->recommendations()->all(),
            ),
        ];
    }

    /** @return array<string, mixed> */
    public function history(ExecutivePlan $plan): array
    {
        $summary = $this->historySummary($plan);

        return [
            'scopeKey' => $plan->scopeKey(),
            'counts' => $summary,
            'decisions' => array_map(
                fn (ExecutiveDecision $decision): array => $this->decisionToArray($decision),
                array_values(array_filter(
                    $plan->decisions()->all(),
                    static fn (ExecutiveDecision $decision): bool => DecisionStatus::Pending !== $decision->status(),
                )),
            ),
        ];
    }

    /** @return array<string, mixed> */
    public function decisionToArray(
        ExecutiveDecision $decision,
        ?GoalPortfolio $portfolio = null,
        ?MentorPlan $mentorPlan = null,
        ?KnowledgeGraph $graph = null,
    ): array {
        $payload = [
            'id' => $decision->id(),
            'type' => $decision->type()->value,
            'status' => $decision->status()->value,
            'priority' => $decision->priority()->value,
            'title' => $decision->title(),
            'summary' => $decision->summary(),
            'reason' => [
                'summary' => $decision->reason()->summary(),
                'detail' => $decision->reason()->detail(),
            ],
            'evidence' => $decision->evidence(),
            'impacts' => array_map(static fn ($impact): string => $impact->value, $decision->impacts()),
            'linkedGoalId' => $decision->linkedGoalId(),
            'linkedConceptKey' => $decision->linkedConceptKey(),
            'linkedResourceId' => $decision->linkedResourceId(),
        ];

        if (null !== $portfolio && null !== $mentorPlan && null !== $graph) {
            $payload['explanation'] = $this->explanationBuilder->build($decision, $portfolio, $mentorPlan, $graph);
        }

        return $payload;
    }

    /** @return array{approved: int, rejected: int, deferred: int, ignored: int} */
    private function historySummary(ExecutivePlan $plan): array
    {
        $summary = [
            'approved' => 0,
            'rejected' => 0,
            'deferred' => 0,
            'ignored' => 0,
        ];

        foreach ($plan->decisions()->all() as $decision) {
            match ($decision->status()) {
                DecisionStatus::Approved => ++$summary['approved'],
                DecisionStatus::Rejected => ++$summary['rejected'],
                DecisionStatus::Deferred => ++$summary['deferred'],
                DecisionStatus::Ignored => ++$summary['ignored'],
                default => null,
            };
        }

        return $summary;
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
            'scheduledAt' => $task->scheduledAt()?->format(DATE_ATOM),
        ];
    }

    /** @return array<string, mixed> */
    private function recommendationToArray(ExecutiveRecommendation $recommendation): array
    {
        return [
            'id' => $recommendation->id(),
            'type' => $recommendation->type()->value,
            'title' => $recommendation->title(),
            'detail' => $recommendation->detail(),
            'priority' => $recommendation->priority()->value,
            'conceptKey' => $recommendation->conceptKey(),
            'resourceId' => $recommendation->resourceId(),
        ];
    }

    /** @return array<string, mixed> */
    private function weeklyReviewToArray(ExecutivePlan $plan): array
    {
        $review = $plan->weeklyReview();

        return [
            'summary' => $review->summary(),
            'progressPercent' => $review->progressPercent(),
            'knowledgeGrowth' => $review->knowledgeGrowth(),
            'completedMissions' => $review->completedMissions(),
            'missedReviews' => $review->missedReviews(),
            'learningMinutes' => $review->learningMinutes(),
            'recommendations' => $review->recommendations(),
            'nextWeekPlan' => $review->nextWeekPlan(),
        ];
    }
}
