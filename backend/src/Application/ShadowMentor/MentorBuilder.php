<?php

declare(strict_types=1);

namespace App\Application\ShadowMentor;

use App\Application\ShadowKnowledge\KnowledgeBuilder;
use App\Domain\ShadowGoals\CareerGoal;
use App\Domain\ShadowGoals\GoalPortfolio;
use App\Domain\ShadowGoals\LearningGoal;
use App\Domain\ShadowGoals\ShadowGoalsRepositoryInterface;
use App\Domain\ShadowMentor\MentorPlan;
use App\Domain\ShadowMentor\ShadowMentorRepositoryInterface;

final class MentorBuilder
{
    public function __construct(
        private readonly ShadowGoalsRepositoryInterface $goalsRepository,
        private readonly ShadowMentorRepositoryInterface $mentorRepository,
        private readonly GoalPlanner $goalPlanner,
        private readonly KnowledgeBuilder $knowledgeBuilder,
        private readonly GoalProgressCalculator $progressCalculator,
    ) {
    }

    public function getPortfolio(string $scopeKey = 'default'): GoalPortfolio
    {
        return $this->goalsRepository->findByScope($scopeKey) ?? GoalPortfolio::create(scopeKey: $scopeKey);
    }

    public function getPlan(string $scopeKey = 'default'): MentorPlan
    {
        return $this->mentorRepository->findByScope($scopeKey) ?? MentorPlan::create(scopeKey: $scopeKey);
    }

    public function syncPlan(string $scopeKey = 'default'): MentorPlan
    {
        $portfolio = $this->ensureDefaultGoal($this->getPortfolio($scopeKey));
        $graph = $this->knowledgeBuilder->syncGraph($scopeKey);
        $portfolio = $this->applyProgress($portfolio, $graph);
        $plan = $this->goalPlanner->plan($portfolio, $this->getPlan($scopeKey), $graph);

        $this->goalsRepository->save($portfolio);
        $this->mentorRepository->save($plan);

        return $plan;
    }

    /** @param array<string, mixed> $payload */
    public function recordQuestion(string $scopeKey, array $payload): MentorPlan
    {
        $this->knowledgeBuilder->recordQuestion($scopeKey, $payload);

        return $this->syncPlan($scopeKey);
    }

    /** @param array<string, mixed> $payload */
    public function createGoal(string $scopeKey, array $payload): GoalPortfolio
    {
        $portfolio = $this->getPortfolio($scopeKey);
        $title = is_string($payload['title'] ?? null) ? trim($payload['title']) : 'New goal';

        $goal = CareerGoal::create($title, is_string($payload['motivation'] ?? null) ? $payload['motivation'] : '')
            ->applyUpdate($payload);

        if (($payload['priority'] ?? null) === 'primary') {
            $portfolio = $this->demoteExistingPrimary($portfolio);
        }

        $portfolio = $portfolio->addGoal($goal);
        $this->goalsRepository->save($portfolio);
        $this->syncPlan($scopeKey);

        return $portfolio;
    }

    /** @param array<string, mixed> $payload */
    public function updateGoal(string $scopeKey, string $goalId, array $payload): GoalPortfolio
    {
        $portfolio = $this->getPortfolio($scopeKey);
        $goal = $portfolio->goals()->find($goalId);

        if (null === $goal) {
            throw new \App\Domain\ShadowGoals\Exception\InvalidShadowGoalException('Goal not found.');
        }

        $portfolio = $portfolio->updateGoal($goal->applyUpdate($payload));
        $this->goalsRepository->save($portfolio);
        $this->syncPlan($scopeKey);

        return $portfolio;
    }

    public function deleteGoal(string $scopeKey, string $goalId): GoalPortfolio
    {
        $portfolio = $this->getPortfolio($scopeKey)->removeGoal($goalId);
        $this->goalsRepository->save($portfolio);
        $this->syncPlan($scopeKey);

        return $portfolio;
    }

    public function completeMission(string $scopeKey, string $missionId): MentorPlan
    {
        $plan = $this->getPlan($scopeKey)->completeMission($missionId);
        $this->mentorRepository->save($plan);
        $this->syncPlan($scopeKey);

        return $this->getPlan($scopeKey);
    }

    public function resetGoals(string $scopeKey = 'default'): GoalPortfolio
    {
        $portfolio = $this->getPortfolio($scopeKey)->reset();
        $plan = $this->getPlan($scopeKey)->reset();
        $this->goalsRepository->save($portfolio);
        $this->mentorRepository->save($plan);

        return $this->ensureDefaultGoal($portfolio);
    }

    public function approveWeeklyReview(string $scopeKey = 'default'): MentorPlan
    {
        $plan = $this->getPlan($scopeKey)->withWeeklyReview(
            $this->getPlan($scopeKey)->weeklyReview()->approveAdaptation(),
        );
        $this->mentorRepository->save($plan);

        return $this->syncPlan($scopeKey);
    }

    private function ensureDefaultGoal(GoalPortfolio $portfolio): GoalPortfolio
    {
        if ([] !== $portfolio->goals()->all()) {
            return $portfolio;
        }

        $portfolio = $portfolio->addGoal(CareerGoal::create('Senior Backend Developer', 'Lead backend teams with confidence.'));
        $this->goalsRepository->save($portfolio);

        return $portfolio;
    }

    private function demoteExistingPrimary(GoalPortfolio $portfolio): GoalPortfolio
    {
        $updated = GoalPortfolio::create($portfolio->id(), $portfolio->scopeKey());

        foreach ($portfolio->goals()->all() as $goal) {
            if (\App\Domain\ShadowGoals\GoalPriority::Primary === $goal->priority()) {
                $updated = $updated->addGoal($goal->applyUpdate(['priority' => 'secondary']));
            } else {
                $updated = $updated->addGoal($goal);
            }
        }

        return $updated;
    }

    private function applyProgress(GoalPortfolio $portfolio, \App\Domain\ShadowKnowledge\KnowledgeGraph $graph): GoalPortfolio
    {
        $updated = GoalPortfolio::create($portfolio->id(), $portfolio->scopeKey());

        foreach ($portfolio->goals()->all() as $goal) {
            $updated = $updated->addGoal(
                $goal->withProgress($this->progressCalculator->percentForGoal($goal, $graph)),
            );
        }

        return $updated;
    }
}
