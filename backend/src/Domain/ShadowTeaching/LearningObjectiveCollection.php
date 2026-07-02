<?php

declare(strict_types=1);

namespace App\Domain\ShadowTeaching;

final readonly class LearningObjectiveCollection
{
    /** @param list<LearningObjective> $items */
    public function __construct(private array $items)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /** @return list<LearningObjective> */
    public function all(): array
    {
        return $this->items;
    }

    public function find(string $key): ?LearningObjective
    {
        foreach ($this->items as $item) {
            if ($item->key() === $key) {
                return $item;
            }
        }

        return null;
    }

    public function upsert(LearningObjective $objective): self
    {
        $items = [];

        foreach ($this->items as $existing) {
            if ($existing->key() === $objective->key()) {
                continue;
            }

            $items[] = $existing;
        }

        $items[] = $objective;

        return new self($items);
    }
}
