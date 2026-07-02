<?php

declare(strict_types=1);

namespace App\Application\ShadowExecutive;

use App\Domain\ShadowExecutive\DecisionType;
use App\Domain\ShadowExecutive\ExecutivePriority;
use App\Domain\ShadowExecutive\ExecutiveRecommendation;
use App\Domain\ShadowExecutive\ExecutiveRecommendationCollection;
use App\Domain\ShadowGoals\GoalPortfolio;
use App\Domain\ShadowKnowledge\KnowledgeGraph;
use App\Domain\ShadowTeaching\TeachingPlan;

final class OpportunityEngine
{
    public function __construct(
        private readonly LearningOpportunityDetector $detector,
    ) {
    }

    public function recommend(
        KnowledgeGraph $graph,
        TeachingPlan $teaching,
        GoalPortfolio $portfolio,
    ): ExecutiveRecommendationCollection {
        $collection = ExecutiveRecommendationCollection::empty();

        foreach ($this->detector->detect($graph, $teaching, $portfolio) as $opportunity) {
            $collection = $collection->append(
                ExecutiveRecommendation::create(
                    DecisionType::RecommendRevision,
                    $opportunity->label(),
                    $opportunity->detail(),
                    ExecutivePriority::Normal,
                    null,
                    null,
                ),
            );
        }

        return $collection;
    }
}
