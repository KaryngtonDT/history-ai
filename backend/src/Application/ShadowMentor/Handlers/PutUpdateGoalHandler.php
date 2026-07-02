<?php

declare(strict_types=1);

namespace App\Application\ShadowMentor\Handlers;

use App\Application\ShadowMentor\GoalJsonMapper;
use App\Application\ShadowMentor\MentorBuilder;

final class PutUpdateGoalHandler
{
    public function __construct(
        private readonly MentorBuilder $builder,
        private readonly GoalJsonMapper $mapper,
    ) {
    }

    /** @param array<string, mixed> $payload */
    /** @return array<string, mixed> */
    public function __invoke(string $scopeKey, string $goalId, array $payload): array
    {
        $portfolio = $this->builder->updateGoal($scopeKey, $goalId, $payload);
        $goal = $portfolio->goals()->find($goalId);

        if (null === $goal) {
            return ['error' => 'Goal not found.'];
        }

        return $this->mapper->goalToArray($goal);
    }
}
