<?php

declare(strict_types=1);

namespace App\Infrastructure\ShadowGoals;

use App\Domain\ShadowGoals\GoalPortfolio;
use App\Domain\ShadowGoals\GoalPortfolioId;
use App\Domain\ShadowGoals\ShadowGoalsRepositoryInterface;
use App\Infrastructure\Storage\JsonFileStore;

final class FileShadowGoalsRepository implements ShadowGoalsRepositoryInterface
{
    public function __construct(
        private readonly JsonFileStore $store,
        private readonly ShadowGoalsPersistenceMapper $mapper,
    ) {
    }

    public function findByScope(string $scopeKey): ?GoalPortfolio
    {
        foreach ($this->store->listJsonFiles() as $filename) {
            $portfolio = $this->read($filename);

            if (null !== $portfolio && $portfolio->scopeKey() === $scopeKey) {
                return $portfolio;
            }
        }

        return null;
    }

    public function findById(GoalPortfolioId $id): ?GoalPortfolio
    {
        return $this->read($id->value.'.json');
    }

    public function save(GoalPortfolio $portfolio): void
    {
        $this->store->write(
            $portfolio->id()->value.'.json',
            $this->mapper->toArray($portfolio),
        );
    }

    private function read(string $filename): ?GoalPortfolio
    {
        $data = $this->store->read($filename);

        if (null === $data) {
            return null;
        }

        return $this->mapper->fromJson(json_encode($data, JSON_THROW_ON_ERROR));
    }
}
