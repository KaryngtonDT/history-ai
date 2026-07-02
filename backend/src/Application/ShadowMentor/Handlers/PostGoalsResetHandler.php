<?php

declare(strict_types=1);

namespace App\Application\ShadowMentor\Handlers;

use App\Application\ShadowMentor\GoalJsonMapper;
use App\Application\ShadowMentor\MentorBuilder;

final class PostGoalsResetHandler
{
    public function __construct(
        private readonly MentorBuilder $builder,
        private readonly GoalJsonMapper $mapper,
    ) {
    }

    /** @return array<string, mixed> */
    public function __invoke(string $scopeKey = 'default'): array
    {
        return $this->mapper->portfolioToArray($this->builder->resetGoals($scopeKey));
    }
}
