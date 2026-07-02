<?php

declare(strict_types=1);

namespace App\Domain\ShadowExecutive;

final readonly class ExecutiveDecisionCollection
{
    /** @param list<ExecutiveDecision> $items */
    public function __construct(private array $items)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /** @return list<ExecutiveDecision> */
    public function all(): array
    {
        return $this->items;
    }

    public function find(string $id): ?ExecutiveDecision
    {
        foreach ($this->items as $item) {
            if ($item->id() === $id) {
                return $item;
            }
        }

        return null;
    }

    public function pending(): self
    {
        $items = [];

        foreach ($this->items as $item) {
            if (DecisionStatus::Pending === $item->status()) {
                $items[] = $item;
            }
        }

        return new self($items);
    }

    public function append(ExecutiveDecision $decision): self
    {
        return new self([...$this->items, $decision]);
    }

    public function upsert(ExecutiveDecision $decision): self
    {
        $items = [];

        foreach ($this->items as $item) {
            if ($item->id() !== $decision->id()) {
                $items[] = $item;
            }
        }

        $items[] = $decision;

        return new self($items);
    }
}
