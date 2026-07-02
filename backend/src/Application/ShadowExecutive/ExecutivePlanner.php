<?php

declare(strict_types=1);

namespace App\Application\ShadowExecutive;

use App\Application\ShadowTeaching\TeachingBuilder;
use App\Domain\ShadowExecutive\DecisionStatus;
use App\Domain\ShadowExecutive\ExecutiveDecision;
use App\Domain\ShadowExecutive\ExecutiveDecisionCollection;
use App\Domain\ShadowExecutive\ExecutivePlan;
use App\Domain\ShadowExecutive\ExecutiveWeeklyReview;
use App\Domain\ShadowGoals\GoalPortfolio;
use App\Domain\ShadowKnowledge\KnowledgeGraph;
use App\Domain\ShadowMentor\MentorPlan;

final class ExecutivePlanner
{
    public function __construct(
        private readonly ReviewScheduler $reviewScheduler,
        private readonly LearningOpportunityDetector $opportunityDetector,
        private readonly ExecutiveDecisionBuilder $decisionBuilder,
        private readonly ExecutiveAgendaBuilder $agendaBuilder,
        private readonly ResourceRecommendationEngine $resourceEngine,
        private readonly OpportunityEngine $opportunityEngine,
        private readonly EnergyAwarePlanner $energyAwarePlanner,
        private readonly TeachingBuilder $teachingBuilder,
    ) {
    }

    public function plan(
        GoalPortfolio $portfolio,
        MentorPlan $mentorPlan,
        KnowledgeGraph $graph,
        ExecutivePlan $existing,
    ): ExecutivePlan {
        if (!$existing->executiveEnabled() || !$portfolio->mentorEnabled()) {
            return $existing;
        }

        $teaching = $this->teachingBuilder->syncPlan($portfolio->scopeKey());
        $staleConcepts = $this->reviewScheduler->findStaleConcepts($graph);
        $primary = $portfolio->primaryGoal();

        $opportunities = $this->opportunityDetector->detect($graph, $teaching, $portfolio);

        $generatedDecisions = $this->decisionBuilder->build(
            $primary,
            $graph,
            $staleConcepts,
            $opportunities,
        );

        $agenda = $this->agendaBuilder->build($mentorPlan, $teaching, $staleConcepts);
        $recommendations = $this->resourceEngine->recommend($mentorPlan, $teaching);

        foreach ($this->opportunityEngine->recommend($graph, $teaching, $portfolio)->all() as $recommendation) {
            $recommendations = $recommendations->append($recommendation);
        }

        $plan = $existing
            ->withAgenda($agenda)
            ->withDecisions($this->mergeDecisions($existing->decisions(), $generatedDecisions))
            ->withRecommendations($recommendations)
            ->withWeeklyReview($this->weeklyReview($primary, $mentorPlan, $graph, $staleConcepts));

        $filteredAgenda = $this->energyAwarePlanner->filterAgenda($plan);

        return $plan->withAgenda($filteredAgenda);
    }

    private function mergeDecisions(
        ExecutiveDecisionCollection $existing,
        ExecutiveDecisionCollection $generated,
    ): ExecutiveDecisionCollection {
        $preserved = [];
        $ignoredConceptKeys = [];

        foreach ($existing->all() as $decision) {
            if (DecisionStatus::Pending !== $decision->status()) {
                $preserved[] = $decision;

                if (DecisionStatus::Ignored === $decision->status()) {
                    if (null !== $decision->linkedConceptKey()) {
                        $ignoredConceptKeys[$decision->linkedConceptKey()] = true;
                    }
                }
            }
        }

        $pending = [];

        foreach ($generated->all() as $decision) {
            if (null !== $decision->linkedConceptKey() && isset($ignoredConceptKeys[$decision->linkedConceptKey()])) {
                continue;
            }

            $matched = $this->findMatchingPending($existing, $decision);
            $pending[] = $matched ?? $decision;
        }

        return new ExecutiveDecisionCollection([...$preserved, ...$pending]);
    }

    private function findMatchingPending(
        ExecutiveDecisionCollection $existing,
        ExecutiveDecision $candidate,
    ): ?ExecutiveDecision {
        foreach ($existing->all() as $decision) {
            if (DecisionStatus::Pending !== $decision->status()) {
                continue;
            }

            if ($decision->type() !== $candidate->type()) {
                continue;
            }

            if ($decision->linkedConceptKey() !== $candidate->linkedConceptKey()) {
                continue;
            }

            return $decision;
        }

        return null;
    }

    /** @param list<array{conceptKey: string, label: string, reason: string}> $staleConcepts */
    private function weeklyReview(
        ?\App\Domain\ShadowGoals\LearningGoal $primary,
        MentorPlan $mentorPlan,
        KnowledgeGraph $graph,
        array $staleConcepts,
    ): ExecutiveWeeklyReview {
        $mentorReview = $mentorPlan->weeklyReview();
        $completedMissions = count(array_filter(
            $mentorPlan->missions()->all(),
            static fn ($mission): bool => \App\Domain\ShadowMentor\MentorMissionStatus::Completed === $mission->status(),
        ));

        return ExecutiveWeeklyReview::generate(
            '' !== $mentorReview->summary()
                ? $mentorReview->summary()
                : (null !== $primary ? sprintf('Working toward %s.', $primary->title()) : 'Executive weekly review'),
            $primary?->progressPercent() ?? 0,
            count($graph->masteries()->all()),
            $completedMissions,
            count($staleConcepts),
            $primary?->progressPercent() ?? 0,
            $mentorReview->recommendations(),
            null !== $primary
                ? sprintf('Next week: prioritize %s and close review gaps.', $primary->title())
                : 'Next week: review pending decisions and continue missions.',
        );
    }
}
