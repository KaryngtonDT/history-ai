<?php

declare(strict_types=1);

namespace App\Application\Agent\DTO;

use App\Domain\Agent\AgentExecutionResult;
use App\Domain\Agent\AgentStep;

final readonly class AgentExecutionResultDto
{
    /**
     * @param list<AgentExecutionStepResult> $steps
     * @param list<string>                   $plannedTools
     */
    public function __construct(
        public array $steps,
        public string $finalSummary,
        public array $plannedTools,
    ) {
    }

    public static function fromDomain(AgentExecutionResult $result): self
    {
        return new self(
            steps: array_map(
                static fn ($step): AgentExecutionStepResult => AgentExecutionStepResult::fromDomain($step),
                $result->steps()->all(),
            ),
            finalSummary: $result->finalSummary(),
            plannedTools: array_map(
                static fn (AgentStep $step): string => $step->tool()->value,
                $result->plan()->steps()->all(),
            ),
        );
    }
}
