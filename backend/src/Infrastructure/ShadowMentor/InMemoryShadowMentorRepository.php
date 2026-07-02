<?php

declare(strict_types=1);

namespace App\Infrastructure\ShadowMentor;

use App\Domain\ShadowMentor\MentorPlan;
use App\Domain\ShadowMentor\MentorPlanId;
use App\Domain\ShadowMentor\ShadowMentorRepositoryInterface;

final class InMemoryShadowMentorRepository implements ShadowMentorRepositoryInterface
{
    /** @var array<string, MentorPlan> */
    private array $plans = [];

    public function findByScope(string $scopeKey): ?MentorPlan
    {
        foreach ($this->plans as $plan) {
            if ($plan->scopeKey() === $scopeKey) {
                return $plan;
            }
        }

        return null;
    }

    public function findById(MentorPlanId $id): ?MentorPlan
    {
        return $this->plans[$id->value] ?? null;
    }

    public function save(MentorPlan $plan): void
    {
        $this->plans[$plan->id()->value] = $plan;
    }
}
