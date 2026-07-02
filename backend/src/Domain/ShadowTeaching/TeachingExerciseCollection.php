<?php

declare(strict_types=1);

namespace App\Domain\ShadowTeaching;

final readonly class TeachingExerciseCollection
{
    /** @param list<TeachingExercise> $items */
    public function __construct(private array $items)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /** @return list<TeachingExercise> */
    public function all(): array
    {
        return $this->items;
    }

    public function find(string $id): ?TeachingExercise
    {
        foreach ($this->items as $item) {
            if ($item->id() === $id) {
                return $item;
            }
        }

        return null;
    }

    public function upsert(TeachingExercise $exercise): self
    {
        $items = [];

        foreach ($this->items as $existing) {
            if ($existing->id() === $exercise->id()) {
                continue;
            }

            $items[] = $existing;
        }

        $items[] = $exercise;

        return new self($items);
    }

    public function forObjective(string $objectiveKey): self
    {
        return new self(array_values(array_filter(
            $this->items,
            static fn (TeachingExercise $exercise): bool => $exercise->objectiveKey() === $objectiveKey,
        )));
    }
}
