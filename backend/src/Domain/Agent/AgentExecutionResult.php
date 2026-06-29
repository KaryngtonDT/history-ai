<?php

declare(strict_types=1);

namespace App\Domain\Agent;

use App\Domain\Agent\Exception\InvalidAgentPlanException;

final readonly class AgentExecutionResult
{
    private string $finalSummary;

    public function __construct(
        private AgentPlan $plan,
        private AgentExecutionStepCollection $steps,
        string $finalSummary,
        private AgentMetadata $metadata = new AgentMetadata([]),
    ) {
        $trimmed = trim($finalSummary);

        if ('' === $trimmed) {
            throw new InvalidAgentPlanException('Agent execution final summary cannot be empty.');
        }

        $this->finalSummary = $trimmed;
    }

    public function plan(): AgentPlan
    {
        return $this->plan;
    }

    public function steps(): AgentExecutionStepCollection
    {
        return $this->steps;
    }

    public function finalSummary(): string
    {
        return $this->finalSummary;
    }

    public function metadata(): AgentMetadata
    {
        return $this->metadata;
    }
}
