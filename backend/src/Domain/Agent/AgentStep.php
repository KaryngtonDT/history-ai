<?php

declare(strict_types=1);

namespace App\Domain\Agent;

use App\Domain\Agent\Exception\InvalidAgentPlanException;

final readonly class AgentStep
{
    private string $description;

    public function __construct(
        private int $order,
        private AgentTool $tool,
        string $description,
    ) {
        if ($order < 0) {
            throw new InvalidAgentPlanException(
                sprintf('Agent step order must be >= 0, got %d.', $order),
            );
        }

        $trimmed = trim($description);

        if ('' === $trimmed) {
            throw new InvalidAgentPlanException('Agent step description cannot be empty.');
        }

        $this->description = $trimmed;
    }

    public function order(): int
    {
        return $this->order;
    }

    public function tool(): AgentTool
    {
        return $this->tool;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function equals(self $other): bool
    {
        return $this->order === $other->order
            && $this->tool === $other->tool
            && $this->description === $other->description;
    }
}
