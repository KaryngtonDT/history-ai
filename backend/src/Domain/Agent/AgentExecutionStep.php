<?php

declare(strict_types=1);

namespace App\Domain\Agent;

use App\Domain\Agent\Exception\InvalidAgentPlanException;

final readonly class AgentExecutionStep
{
    private string $summary;

    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        private int $order,
        private AgentTool $tool,
        private AgentExecutionStatus $status,
        string $summary,
        private array $metadata = [],
    ) {
        if ($order < 0) {
            throw new InvalidAgentPlanException(
                sprintf('Agent execution step order must be >= 0, got %d.', $order),
            );
        }

        $trimmed = trim($summary);

        if ('' === $trimmed) {
            throw new InvalidAgentPlanException('Agent execution step summary cannot be empty.');
        }

        $this->summary = $trimmed;
    }

    public function order(): int
    {
        return $this->order;
    }

    public function tool(): AgentTool
    {
        return $this->tool;
    }

    public function status(): AgentExecutionStatus
    {
        return $this->status;
    }

    public function summary(): string
    {
        return $this->summary;
    }

    /**
     * @return array<string, mixed>
     */
    public function metadata(): array
    {
        return $this->metadata;
    }

    public function equals(self $other): bool
    {
        return $this->order === $other->order
            && $this->tool === $other->tool
            && $this->status === $other->status
            && $this->summary === $other->summary
            && $this->metadata === $other->metadata;
    }
}
