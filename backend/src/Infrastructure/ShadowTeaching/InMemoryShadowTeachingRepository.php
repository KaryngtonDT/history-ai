<?php

declare(strict_types=1);

namespace App\Infrastructure\ShadowTeaching;

use App\Domain\ShadowTeaching\ShadowTeachingRepositoryInterface;
use App\Domain\ShadowTeaching\TeachingPlan;
use App\Domain\ShadowTeaching\TeachingPlanId;

final class InMemoryShadowTeachingRepository implements ShadowTeachingRepositoryInterface
{
    /** @var array<string, TeachingPlan> */
    private array $plans = [];

    public function findByScope(string $scopeKey): ?TeachingPlan
    {
        foreach ($this->plans as $plan) {
            if ($plan->scopeKey() === $scopeKey) {
                return $plan;
            }
        }

        return null;
    }

    public function findById(TeachingPlanId $id): ?TeachingPlan
    {
        return $this->plans[$id->value] ?? null;
    }

    public function save(TeachingPlan $plan): void
    {
        $this->plans[$plan->id()->value] = $plan;
    }
}
