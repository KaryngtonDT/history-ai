<?php

declare(strict_types=1);

namespace App\Domain\Agent;

interface AgentPlannerInterface
{
    public function plan(AgentRequest $request): AgentPlan;
}
