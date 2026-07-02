<?php

declare(strict_types=1);

namespace App\Domain\ShadowMentor;

interface ShadowMentorRepositoryInterface
{
    public function findByScope(string $scopeKey): ?MentorPlan;

    public function findById(MentorPlanId $id): ?MentorPlan;

    public function save(MentorPlan $plan): void;
}
