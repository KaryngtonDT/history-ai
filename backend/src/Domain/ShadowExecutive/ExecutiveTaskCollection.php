<?php

declare(strict_types=1);

namespace App\Domain\ShadowExecutive;

final readonly class ExecutiveTaskCollection
{
    /** @param list<ExecutiveTask> $items */
    public function __construct(private array $items)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /** @return list<ExecutiveTask> */
    public function all(): array
    {
        return $this->items;
    }

    public function find(string $id): ?ExecutiveTask
    {
        foreach ($this->items as $item) {
            if ($item->id() === $id) {
                return $item;
            }
        }

        return null;
    }

    public function append(ExecutiveTask $task): self
    {
        return new self([...$this->items, $task]);
    }

    public function upsert(ExecutiveTask $task): self
    {
        $items = [];

        foreach ($this->items as $item) {
            if ($item->id() !== $task->id()) {
                $items[] = $item;
            }
        }

        $items[] = $task;

        return new self($items);
    }
}
