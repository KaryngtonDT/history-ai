<?php

declare(strict_types=1);

namespace App\Domain\Agent;

use App\Domain\Agent\Exception\InvalidAgentPlanException;

final readonly class AgentPlan
{
    public function __construct(
        private AgentStepCollection $steps,
    ) {
    }

    public static function empty(): self
    {
        return new self(AgentStepCollection::empty());
    }

    public function steps(): AgentStepCollection
    {
        return $this->steps;
    }

    public function append(AgentTool $tool, string $description): self
    {
        if (!$this->steps->isEmpty()) {
            $existingSteps = $this->steps->all();
            $lastStep = $existingSteps[count($existingSteps) - 1];

            if ($lastStep->tool() === $tool) {
                throw new InvalidAgentPlanException(
                    sprintf(
                        'Consecutive agent steps cannot use the same tool "%s".',
                        $tool->value,
                    ),
                );
            }
        }

        $nextOrder = $this->steps->count();
        $step = new AgentStep($nextOrder, $tool, $description);

        return new self($this->steps->append($step));
    }

    public function containsTool(AgentTool $tool): bool
    {
        foreach ($this->steps->all() as $step) {
            if ($step->tool() === $tool) {
                return true;
            }
        }

        return false;
    }

    public function toolCount(): int
    {
        return $this->steps->count();
    }
}
