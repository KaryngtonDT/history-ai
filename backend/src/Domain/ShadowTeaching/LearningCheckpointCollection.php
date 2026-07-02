<?php

declare(strict_types=1);

namespace App\Domain\ShadowTeaching;

final readonly class LearningCheckpointCollection
{
    /** @param list<LearningCheckpoint> $items */
    public function __construct(private array $items)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /** @return list<LearningCheckpoint> */
    public function all(): array
    {
        return $this->items;
    }

    public function find(string $id): ?LearningCheckpoint
    {
        foreach ($this->items as $item) {
            if ($item->id() === $id) {
                return $item;
            }
        }

        return null;
    }

    public function upsert(LearningCheckpoint $checkpoint): self
    {
        $items = [];

        foreach ($this->items as $existing) {
            if ($existing->id() === $checkpoint->id()) {
                continue;
            }

            $items[] = $existing;
        }

        $items[] = $checkpoint;

        return new self($items);
    }
}
