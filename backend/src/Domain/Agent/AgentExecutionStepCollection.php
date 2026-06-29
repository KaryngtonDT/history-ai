<?php

declare(strict_types=1);

namespace App\Domain\Agent;

use App\Domain\Agent\Exception\InvalidAgentPlanException;

final readonly class AgentExecutionStepCollection
{
    /** @var list<AgentExecutionStep> */
    private array $steps;

    /**
     * @param list<AgentExecutionStep> $steps
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
     * @return list<AgentExecutionStep>
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

    public function append(AgentExecutionStep $step): self
    {
        $expectedOrder = count($this->steps);

        if ($step->order() !== $expectedOrder) {
            throw new InvalidAgentPlanException(
                sprintf(
                    'Agent execution step order must be sequential, expected %d, got %d.',
                    $expectedOrder,
                    $step->order(),
                ),
            );
        }

        return new self([...$this->steps, $step]);
    }

    /**
     * @param list<AgentExecutionStep> $steps
     */
    private function assertSequentialOrder(array $steps): void
    {
        foreach ($steps as $index => $step) {
            if ($step->order() !== $index) {
                throw new InvalidAgentPlanException(
                    sprintf(
                        'Agent execution steps must be ordered sequentially from 0, expected %d, got %d.',
                        $index,
                        $step->order(),
                    ),
                );
            }
        }
    }
}
