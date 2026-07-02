<?php

declare(strict_types=1);

namespace App\Application\ShadowExecutive;

use App\Domain\ShadowExecutive\DecisionImpact;
use App\Domain\ShadowExecutive\DecisionType;
use App\Domain\ShadowExecutive\ExecutiveDecision;
use App\Domain\ShadowExecutive\ExecutiveDecisionCollection;
use App\Domain\ShadowExecutive\ExecutiveOpportunity;
use App\Domain\ShadowExecutive\ExecutivePriority;
use App\Domain\ShadowExecutive\ExecutiveReason;
use App\Domain\ShadowGoals\LearningGoal;
use App\Domain\ShadowKnowledge\KnowledgeGraph;

final class ExecutiveDecisionBuilder
{
    public function __construct(
        private readonly PriorityResolver $priorityResolver,
    ) {
    }

    /** @param list<array{conceptKey: string, label: string, reason: string, masteryPercent: int, exposureCount: int, daysSinceReview: ?int}> $staleConcepts */
    /** @param list<ExecutiveOpportunity> $opportunities */
    public function build(
        ?LearningGoal $primaryGoal,
        KnowledgeGraph $graph,
        array $staleConcepts,
        array $opportunities,
    ): ExecutiveDecisionCollection {
        $collection = ExecutiveDecisionCollection::empty();

        foreach ($staleConcepts as $stale) {
            $mastery = $graph->masteries()->find($stale['conceptKey']);
            $priority = $this->priorityResolver->resolve(
                $primaryGoal,
                $graph,
                $stale['conceptKey'],
                $stale['masteryPercent'],
                $mastery?->lastSeenAt(),
            );

            $collection = $collection->append(
                ExecutiveDecision::create(
                    DecisionType::Review,
                    sprintf('Review %s', $stale['label']),
                    $stale['reason'],
                    ExecutiveReason::create($stale['reason'], 'Scheduled from knowledge graph mastery signals.'),
                    $priority,
                    [
                        sprintf('knowledge:mastery:%s', $stale['conceptKey']),
                        sprintf('knowledge:exposure:%d', $stale['exposureCount']),
                    ],
                    [DecisionImpact::Knowledge, DecisionImpact::Confidence],
                    $primaryGoal?->id(),
                    $stale['conceptKey'],
                ),
            );
        }

        foreach ($opportunities as $opportunity) {
            $collection = $collection->append(
                ExecutiveDecision::create(
                    DecisionType::RecommendRevision,
                    $opportunity->label(),
                    $opportunity->detail(),
                    ExecutiveReason::create($opportunity->label(), $opportunity->detail()),
                    ExecutivePriority::Normal,
                    [sprintf('%s:opportunity', $opportunity->source())],
                    [DecisionImpact::Knowledge, DecisionImpact::Goal],
                    $primaryGoal?->id(),
                ),
            );
        }

        if (null !== $primaryGoal && $primaryGoal->progressPercent() >= 70) {
            $collection = $collection->append(
                ExecutiveDecision::create(
                    DecisionType::Accelerate,
                    'Increase learning pace',
                    sprintf('You are %d%% toward %s — consider accelerating.', $primaryGoal->progressPercent(), $primaryGoal->title()),
                    ExecutiveReason::create('Strong progress on primary goal.', 'Momentum supports a faster weekly rhythm.'),
                    ExecutivePriority::Normal,
                    [sprintf('mentor:goal:%s', $primaryGoal->id())],
                    [DecisionImpact::Goal, DecisionImpact::Time],
                    $primaryGoal->id(),
                ),
            );
        }

        return $collection;
    }
}
