<?php

declare(strict_types=1);

namespace App\Domain\Agent;

final readonly class AgentToolExecutionResult
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        private AgentTool $tool,
        private string $summary,
        private array $metadata,
    ) {
    }

    public static function empty(): self
    {
        return new self(AgentTool::SemanticSearch, '', []);
    }

    public function tool(): AgentTool
    {
        return $this->tool;
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
        return $this->tool === $other->tool
            && $this->summary === $other->summary
            && $this->metadata === $other->metadata;
    }
}
