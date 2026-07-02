<?php

declare(strict_types=1);

namespace App\Application\ShadowExecutive;

use App\Application\ShadowKnowledge\KnowledgeBuilder;
use App\Application\ShadowMentor\MentorBuilder;
use App\Domain\ShadowExecutive\Exception\InvalidShadowExecutiveException;
use App\Domain\ShadowExecutive\ExecutivePlan;
use App\Domain\ShadowExecutive\ShadowExecutiveRepositoryInterface;

final class ExecutiveCoordinator
{
    public function __construct(
        private readonly MentorBuilder $mentorBuilder,
        private readonly ShadowExecutiveRepositoryInterface $repository,
        private readonly ExecutivePlanner $planner,
        private readonly KnowledgeBuilder $knowledgeBuilder,
    ) {
    }

    public function getPlan(string $scopeKey = 'default'): ExecutivePlan
    {
        return $this->repository->findByScope($scopeKey) ?? ExecutivePlan::create(scopeKey: $scopeKey);
    }

    public function syncPlan(string $scopeKey = 'default'): ExecutivePlan
    {
        $mentorPlan = $this->mentorBuilder->syncPlan($scopeKey);
        $graph = $this->knowledgeBuilder->syncGraph($scopeKey);
        $portfolio = $this->mentorBuilder->getPortfolio($scopeKey);
        $existing = $this->getPlan($scopeKey);
        $plan = $this->planner->plan($portfolio, $mentorPlan, $graph, $existing);

        $this->repository->save($plan);

        return $plan;
    }

    /** @param array<string, mixed> $payload */
    public function recordQuestion(string $scopeKey, array $payload): ExecutivePlan
    {
        $this->mentorBuilder->recordQuestion($scopeKey, $payload);

        return $this->syncPlan($scopeKey);
    }

    public function approveDecision(string $scopeKey, string $decisionId): ExecutivePlan
    {
        return $this->updateDecision($scopeKey, $decisionId, static fn ($decision) => $decision->approve());
    }

    public function rejectDecision(string $scopeKey, string $decisionId): ExecutivePlan
    {
        return $this->updateDecision($scopeKey, $decisionId, static fn ($decision) => $decision->reject());
    }

    public function deferDecision(string $scopeKey, string $decisionId): ExecutivePlan
    {
        return $this->updateDecision($scopeKey, $decisionId, static fn ($decision) => $decision->defer());
    }

    public function reset(string $scopeKey = 'default'): ExecutivePlan
    {
        $plan = $this->getPlan($scopeKey)->reset();
        $this->repository->save($plan);

        return $this->syncPlan($scopeKey);
    }

    /** @param callable(\App\Domain\ShadowExecutive\ExecutiveDecision): \App\Domain\ShadowExecutive\ExecutiveDecision $mutator */
    private function updateDecision(string $scopeKey, string $decisionId, callable $mutator): ExecutivePlan
    {
        $plan = $this->getPlan($scopeKey);
        $decision = $plan->findDecision($decisionId);

        if (null === $decision) {
            throw new InvalidShadowExecutiveException('Executive decision not found.');
        }

        $updated = $mutator($decision);
        $decisions = $plan->decisions()->upsert($updated);
        $plan = $plan->withDecisions($decisions);

        $this->repository->save($plan);

        return $plan;
    }
}
