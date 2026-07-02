<?php

declare(strict_types=1);

namespace App\Application\ShadowMentor\Handlers;

use App\Application\ShadowMentor\MentorBuilder;

final class DeleteGoalHandler
{
    public function __construct(private readonly MentorBuilder $builder)
    {
    }

    public function __invoke(string $scopeKey, string $goalId): void
    {
        $this->builder->deleteGoal($scopeKey, $goalId);
    }
}
