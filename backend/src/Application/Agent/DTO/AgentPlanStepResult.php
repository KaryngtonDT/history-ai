<?php

declare(strict_types=1);

namespace App\Application\Agent\DTO;

use App\Domain\Agent\AgentStep;

final readonly class AgentPlanStepResult
{
    public function __construct(
        public int $order,
        public string $tool,
        public string $description,
    ) {
    }

    public static function fromDomain(AgentStep $step): self
    {
        return new self(
            order: $step->order(),
            tool: $step->tool()->value,
            description: $step->description(),
        );
    }
}
