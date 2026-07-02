<?php

declare(strict_types=1);

namespace App\Infrastructure\ShadowExecutive;

use App\Domain\ShadowExecutive\DecisionImpact;
use App\Domain\ShadowExecutive\DecisionStatus;
use App\Domain\ShadowExecutive\DecisionType;
use App\Domain\ShadowExecutive\Exception\InvalidShadowExecutiveException;
use App\Domain\ShadowExecutive\ExecutiveAgenda;
use App\Domain\ShadowExecutive\ExecutiveConstraint;
use App\Domain\ShadowExecutive\ExecutiveDecision;
use App\Domain\ShadowExecutive\ExecutiveDecisionCollection;
use App\Domain\ShadowExecutive\ExecutivePlan;
use App\Domain\ShadowExecutive\ExecutivePlanId;
use App\Domain\ShadowExecutive\ExecutivePriority;
use App\Domain\ShadowExecutive\ExecutiveReason;
use App\Domain\ShadowExecutive\ExecutiveRecommendation;
use App\Domain\ShadowExecutive\ExecutiveRecommendationCollection;
use App\Domain\ShadowExecutive\ExecutiveTask;
use App\Domain\ShadowExecutive\ExecutiveTaskCollection;
use App\Domain\ShadowExecutive\ExecutiveTaskType;
use App\Domain\ShadowExecutive\ExecutiveWeeklyReview;
use JsonException;

final class ShadowExecutivePersistenceMapper
{
    /** @return array<string, mixed> */
    public function toArray(ExecutivePlan $plan): array
    {
        return [
            'id' => $plan->id()->value,
            'scopeKey' => $plan->scopeKey(),
            'executiveEnabled' => $plan->executiveEnabled(),
            'availableMinutes' => $plan->availableMinutes(),
            'agenda' => $this->agendaToArray($plan->agenda()),
            'decisions' => array_map($this->decisionToArray(...), $plan->decisions()->all()),
            'recommendations' => array_map($this->recommendationToArray(...), $plan->recommendations()->all()),
            'weeklyReview' => $this->weeklyReviewToArray($plan->weeklyReview()),
        ];
    }

    public function fromJson(string $json): ExecutivePlan
    {
        try {
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidShadowExecutiveException('Stored executive plan is not valid JSON.', 0, $exception);
        }

        if (!is_array($decoded) || !is_string($decoded['id'] ?? null)) {
            throw new InvalidShadowExecutiveException('Stored executive plan is invalid.');
        }

        $agenda = is_array($decoded['agenda'] ?? null) ? $decoded['agenda'] : [];
        $review = is_array($decoded['weeklyReview'] ?? null) ? $decoded['weeklyReview'] : [];

        return new ExecutivePlan(
            new ExecutivePlanId($decoded['id']),
            is_string($decoded['scopeKey'] ?? null) ? $decoded['scopeKey'] : 'default',
            $this->agendaFromArray($agenda),
            $this->decisionsFromArray(is_array($decoded['decisions'] ?? null) ? $decoded['decisions'] : []),
            $this->recommendationsFromArray(is_array($decoded['recommendations'] ?? null) ? $decoded['recommendations'] : []),
            $this->weeklyReviewFromArray($review),
            (bool) ($decoded['executiveEnabled'] ?? true),
            isset($decoded['availableMinutes']) ? (int) $decoded['availableMinutes'] : null,
        );
    }

    /** @return array<string, mixed> */
    private function agendaToArray(ExecutiveAgenda $agenda): array
    {
        return [
            'today' => array_map($this->taskToArray(...), $agenda->today()->all()),
            'upcoming' => array_map($this->taskToArray(...), $agenda->upcoming()->all()),
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
            'scheduledAt' => $task->scheduledAt()?->format(DATE_ATOM),
        ];
    }

    /** @return array<string, mixed> */
    private function decisionToArray(ExecutiveDecision $decision): array
    {
        return [
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
            'impacts' => array_map(static fn (DecisionImpact $impact): string => $impact->value, $decision->impacts()),
            'linkedGoalId' => $decision->linkedGoalId(),
            'linkedConceptKey' => $decision->linkedConceptKey(),
            'linkedResourceId' => $decision->linkedResourceId(),
            'constraint' => null !== $decision->constraint()
                ? $this->constraintToArray($decision->constraint())
                : null,
        ];
    }

    /** @return array<string, mixed> */
    private function constraintToArray(ExecutiveConstraint $constraint): array
    {
        return [
            'key' => $constraint->key(),
            'label' => $constraint->label(),
            'detail' => $constraint->detail(),
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
    private function weeklyReviewToArray(ExecutiveWeeklyReview $review): array
    {
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

    /** @param array<string, mixed> $data */
    private function agendaFromArray(array $data): ExecutiveAgenda
    {
        return new ExecutiveAgenda(
            $this->tasksFromArray(is_array($data['today'] ?? null) ? $data['today'] : []),
            $this->tasksFromArray(is_array($data['upcoming'] ?? null) ? $data['upcoming'] : []),
        );
    }

    /** @param list<array<string, mixed>> $items */
    private function tasksFromArray(array $items): ExecutiveTaskCollection
    {
        $collection = ExecutiveTaskCollection::empty();

        foreach ($items as $item) {
            if (!is_array($item) || !is_string($item['id'] ?? null)) {
                continue;
            }

            $collection = $collection->upsert(new ExecutiveTask(
                $item['id'],
                ExecutiveTaskType::tryFrom((string) ($item['type'] ?? 'review')) ?? ExecutiveTaskType::Review,
                is_string($item['label'] ?? null) ? $item['label'] : '',
                is_string($item['detail'] ?? null) ? $item['detail'] : '',
                (int) ($item['order'] ?? 0),
                isset($item['scheduledAt']) && is_string($item['scheduledAt'])
                    ? new \DateTimeImmutable($item['scheduledAt'])
                    : null,
            ));
        }

        return $collection;
    }

    /** @param list<array<string, mixed>> $items */
    private function decisionsFromArray(array $items): ExecutiveDecisionCollection
    {
        $collection = ExecutiveDecisionCollection::empty();

        foreach ($items as $item) {
            if (!is_array($item) || !is_string($item['id'] ?? null)) {
                continue;
            }

            $reason = is_array($item['reason'] ?? null) ? $item['reason'] : [];
            $constraint = is_array($item['constraint'] ?? null) ? $item['constraint'] : null;

            $collection = $collection->upsert(new ExecutiveDecision(
                $item['id'],
                DecisionType::tryFrom((string) ($item['type'] ?? 'review')) ?? DecisionType::Review,
                DecisionStatus::tryFrom((string) ($item['status'] ?? 'pending')) ?? DecisionStatus::Pending,
                ExecutivePriority::tryFrom((string) ($item['priority'] ?? 'normal')) ?? ExecutivePriority::Normal,
                is_string($item['title'] ?? null) ? $item['title'] : '',
                is_string($item['summary'] ?? null) ? $item['summary'] : '',
                new ExecutiveReason(
                    is_string($reason['summary'] ?? null) ? $reason['summary'] : '',
                    is_string($reason['detail'] ?? null) ? $reason['detail'] : '',
                ),
                is_array($item['evidence'] ?? null) ? array_values(array_map('strval', $item['evidence'])) : [],
                $this->impactsFromArray(is_array($item['impacts'] ?? null) ? $item['impacts'] : []),
                is_string($item['linkedGoalId'] ?? null) ? $item['linkedGoalId'] : null,
                is_string($item['linkedConceptKey'] ?? null) ? $item['linkedConceptKey'] : null,
                is_string($item['linkedResourceId'] ?? null) ? $item['linkedResourceId'] : null,
                null !== $constraint && is_string($constraint['key'] ?? null)
                    ? new ExecutiveConstraint(
                        $constraint['key'],
                        is_string($constraint['label'] ?? null) ? $constraint['label'] : $constraint['key'],
                        is_string($constraint['detail'] ?? null) ? $constraint['detail'] : '',
                    )
                    : null,
            ));
        }

        return $collection;
    }

    /** @param list<mixed> $items */
    /** @return list<DecisionImpact> */
    private function impactsFromArray(array $items): array
    {
        $impacts = [];

        foreach ($items as $item) {
            $impact = DecisionImpact::tryFrom((string) $item);

            if (null !== $impact) {
                $impacts[] = $impact;
            }
        }

        return $impacts;
    }

    /** @param list<array<string, mixed>> $items */
    private function recommendationsFromArray(array $items): ExecutiveRecommendationCollection
    {
        $collection = ExecutiveRecommendationCollection::empty();

        foreach ($items as $item) {
            if (!is_array($item) || !is_string($item['id'] ?? null)) {
                continue;
            }

            $collection = $collection->upsert(new ExecutiveRecommendation(
                $item['id'],
                DecisionType::tryFrom((string) ($item['type'] ?? 'review')) ?? DecisionType::Review,
                is_string($item['title'] ?? null) ? $item['title'] : '',
                is_string($item['detail'] ?? null) ? $item['detail'] : '',
                ExecutivePriority::tryFrom((string) ($item['priority'] ?? 'normal')) ?? ExecutivePriority::Normal,
                is_string($item['conceptKey'] ?? null) ? $item['conceptKey'] : null,
                is_string($item['resourceId'] ?? null) ? $item['resourceId'] : null,
            ));
        }

        return $collection;
    }

    /** @param array<string, mixed> $data */
    private function weeklyReviewFromArray(array $data): ExecutiveWeeklyReview
    {
        return new ExecutiveWeeklyReview(
            is_string($data['summary'] ?? null) ? $data['summary'] : '',
            (int) ($data['progressPercent'] ?? 0),
            (int) ($data['knowledgeGrowth'] ?? 0),
            (int) ($data['completedMissions'] ?? 0),
            (int) ($data['missedReviews'] ?? 0),
            (int) ($data['learningMinutes'] ?? 0),
            is_array($data['recommendations'] ?? null) ? array_values(array_map('strval', $data['recommendations'])) : [],
            is_string($data['nextWeekPlan'] ?? null) ? $data['nextWeekPlan'] : '',
        );
    }
}
