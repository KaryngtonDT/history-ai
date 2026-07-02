<?php

declare(strict_types=1);

namespace App\Domain\ShadowExecutive;

interface ShadowExecutiveRepositoryInterface
{
    public function findByScope(string $scopeKey): ?ExecutivePlan;

    public function findById(ExecutivePlanId $id): ?ExecutivePlan;

    public function save(ExecutivePlan $plan): void;
}
