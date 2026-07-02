<?php

declare(strict_types=1);

namespace App\Domain\ShadowTeaching;

interface ShadowTeachingRepositoryInterface
{
    public function findByScope(string $scopeKey): ?TeachingPlan;

    public function findById(TeachingPlanId $id): ?TeachingPlan;

    public function save(TeachingPlan $plan): void;
}
