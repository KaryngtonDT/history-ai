<?php

declare(strict_types=1);

namespace App\Domain\ShadowGoals;

interface ShadowGoalsRepositoryInterface
{
    public function findByScope(string $scopeKey): ?GoalPortfolio;

    public function findById(GoalPortfolioId $id): ?GoalPortfolio;

    public function save(GoalPortfolio $portfolio): void;
}
