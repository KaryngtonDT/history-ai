<?php

declare(strict_types=1);

namespace App\Domain\Agent;

use App\Domain\Agent\Exception\InvalidAgentPlanException;

final readonly class AgentStepCollection
{
    /** @var list<AgentStep> */
    private array $steps;

    /**
     * @param list<AgentStep> $steps
     */
    public function __construct(array $steps)
    {
        $normalized = array_values($steps);
        $this->assertSequentialOrder($normalized);
        $this->steps = $normalized;
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return list<AgentStep>
     */
    public function all(): array
    {
        return $this->steps;
    }

    public function count(): int
    {
        return count($this->steps);
    }

    public function isEmpty(): bool
    {
        return [] === $this->steps;
    }

    public function append(AgentStep $step): self
    {
        $expectedOrder = count($this->steps);

        if ($step->order() !== $expectedOrder) {
            throw new InvalidAgentPlanException(
                sprintf(
                    'Agent step order must be sequential, expected %d, got %d.',
                    $expectedOrder,
                    $step->order(),
                ),
            );
        }

        return new self([...$this->steps, $step]);
    }

    /**
     * @param list<AgentStep> $steps
     */
    private function assertSequentialOrder(array $steps): void
    {
        foreach ($steps as $index => $step) {
            if ($step->order() !== $index) {
                throw new InvalidAgentPlanException(
                    sprintf(
                        'Agent steps must be ordered sequentially from 0, expected %d, got %d.',
                        $index,
                        $step->order(),
                    ),
                );
            }
        }
    }
}
