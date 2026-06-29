<?php

declare(strict_types=1);

namespace App\Domain\Agent;

final readonly class AgentMetadataCollection
{
    /** @var list<AgentMetadata> */
    private array $items;

    /**
     * @param list<AgentMetadata> $items
     */
    public function __construct(array $items)
    {
        $this->items = array_values($items);
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public static function fromExecutionSteps(AgentExecutionStepCollection $steps): self
    {
        $items = [];

        foreach ($steps->all() as $step) {
            $items[] = new AgentMetadata($step->metadata());
        }

        return new self($items);
    }

    public function append(AgentMetadata $metadata): self
    {
        return new self([...$this->items, $metadata]);
    }

    public function merge(): AgentMetadata
    {
        /** @var array<string, mixed> $merged */
        $merged = [];

        foreach ($this->items as $metadata) {
            foreach ($metadata->values() as $key => $value) {
                $merged[$key] = $value;
            }
        }

        return new AgentMetadata($merged);
    }

    /**
     * @return list<AgentMetadata>
     */
    public function all(): array
    {
        return $this->items;
    }

    public function count(): int
    {
        return count($this->items);
    }
}
