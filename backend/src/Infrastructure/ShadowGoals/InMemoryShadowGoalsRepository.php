<?php

declare(strict_types=1);

namespace App\Infrastructure\ShadowGoals;

use App\Domain\ShadowGoals\GoalPortfolio;
use App\Domain\ShadowGoals\GoalPortfolioId;
use App\Domain\ShadowGoals\ShadowGoalsRepositoryInterface;

final class InMemoryShadowGoalsRepository implements ShadowGoalsRepositoryInterface
{
    /** @var array<string, GoalPortfolio> */
    private array $portfolios = [];

    public function findByScope(string $scopeKey): ?GoalPortfolio
    {
        foreach ($this->portfolios as $portfolio) {
            if ($portfolio->scopeKey() === $scopeKey) {
                return $portfolio;
            }
        }

        return null;
    }

    public function findById(GoalPortfolioId $id): ?GoalPortfolio
    {
        return $this->portfolios[$id->value] ?? null;
    }

    public function save(GoalPortfolio $portfolio): void
    {
        $this->portfolios[$portfolio->id()->value] = $portfolio;
    }
}
