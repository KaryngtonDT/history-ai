<?php

declare(strict_types=1);

namespace App\Application\ShadowMentor\Handlers;

use App\Application\ShadowMentor\GoalJsonMapper;
use App\Application\ShadowMentor\MentorBuilder;

final class PostCreateGoalHandler
{
    public function __construct(
        private readonly MentorBuilder $builder,
        private readonly GoalJsonMapper $mapper,
    ) {
    }

    /** @param array<string, mixed> $payload */
    /** @return array<string, mixed> */
    public function __invoke(string $scopeKey, array $payload): array
    {
        $before = $this->builder->getPortfolio($scopeKey);
        $beforeIds = array_map(static fn ($goal) => $goal->id(), $before->goals()->all());
        $portfolio = $this->builder->createGoal($scopeKey, $payload);

        foreach ($portfolio->goals()->all() as $goal) {
            if (!in_array($goal->id(), $beforeIds, true)) {
                return $this->mapper->goalToArray($goal);
            }
        }

        $goals = $portfolio->goals()->all();

        return $this->mapper->goalToArray($goals[array_key_last($goals)]);
    }
}
