<?php

declare(strict_types=1);

namespace App\Application\Agent\DTO;

use App\Domain\Agent\AgentExecutionStep;

final readonly class AgentExecutionStepResult
{
    public function __construct(
        public int $order,
        public string $tool,
        public string $status,
        public string $summary,
    ) {
    }

    public static function fromDomain(AgentExecutionStep $step): self
    {
        return new self(
            order: $step->order(),
            tool: $step->tool()->value,
            status: $step->status()->value,
            summary: $step->summary(),
        );
    }
}
