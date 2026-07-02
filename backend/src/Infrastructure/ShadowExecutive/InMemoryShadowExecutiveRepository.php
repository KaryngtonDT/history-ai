<?php

declare(strict_types=1);

namespace App\Infrastructure\ShadowExecutive;

use App\Domain\ShadowExecutive\ExecutivePlan;
use App\Domain\ShadowExecutive\ExecutivePlanId;
use App\Domain\ShadowExecutive\ShadowExecutiveRepositoryInterface;

final class InMemoryShadowExecutiveRepository implements ShadowExecutiveRepositoryInterface
{
    /** @var array<string, ExecutivePlan> */
    private array $plans = [];

    public function findByScope(string $scopeKey): ?ExecutivePlan
    {
        foreach ($this->plans as $plan) {
            if ($plan->scopeKey() === $scopeKey) {
                return $plan;
            }
        }

        return null;
    }

    public function findById(ExecutivePlanId $id): ?ExecutivePlan
    {
        return $this->plans[$id->value] ?? null;
    }

    public function save(ExecutivePlan $plan): void
    {
        $this->plans[$plan->id()->value] = $plan;
    }
}
