<?php

declare(strict_types=1);

namespace App\Application\Agent\DTO;

use App\Domain\Agent\AgentExecutionResult;

final readonly class AgentExecutionResultDto
{
    /**
     * @param list<AgentPlanStepResult>        $plan
     * @param list<AgentExecutionStepResult> $steps
     * @param array<string, mixed>           $metadata
     */
    public function __construct(
        public array $plan,
        public array $steps,
        public string $finalSummary,
        public array $metadata = [],
    ) {
    }

    public static function fromDomain(AgentExecutionResult $result): self
    {
        return new self(
            plan: array_map(
                static fn ($step): AgentPlanStepResult => AgentPlanStepResult::fromDomain($step),
                $result->plan()->steps()->all(),
            ),
            steps: array_map(
                static fn ($step): AgentExecutionStepResult => AgentExecutionStepResult::fromDomain($step),
                $result->steps()->all(),
            ),
            finalSummary: $result->finalSummary(),
            metadata: $result->metadata()->values(),
        );
    }
}
