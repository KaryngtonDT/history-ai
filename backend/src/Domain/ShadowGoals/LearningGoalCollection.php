<?php

declare(strict_types=1);

namespace App\Domain\ShadowGoals;

final readonly class LearningGoalCollection
{
    /** @param list<LearningGoal> $items */
    public function __construct(private array $items)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /** @param list<LearningGoal> $items */
    public static function from(array $items): self
    {
        return new self(array_values($items));
    }

    /** @return list<LearningGoal> */
    public function all(): array
    {
        return $this->items;
    }

    public function find(string $id): ?LearningGoal
    {
        foreach ($this->items as $item) {
            if ($item->id() === $id) {
                return $item;
            }
        }

        return null;
    }

    public function primary(): ?LearningGoal
    {
        foreach ($this->items as $item) {
            if (GoalPriority::Primary === $item->priority() && GoalStatus::Active === $item->status()) {
                return $item;
            }
        }

        return $this->items[0] ?? null;
    }

    /** @return list<LearningGoal> */
    public function secondary(): array
    {
        return array_values(array_filter(
            $this->items,
            static fn (LearningGoal $goal): bool => GoalPriority::Secondary === $goal->priority()
                && GoalStatus::Active === $goal->status(),
        ));
    }

    public function upsert(LearningGoal $goal): self
    {
        $items = [];

        foreach ($this->items as $item) {
            if ($item->id() !== $goal->id()) {
                $items[] = $item;
            }
        }

        $items[] = $goal;

        return new self($items);
    }

    public function remove(string $id): self
    {
        return new self(array_values(array_filter(
            $this->items,
            static fn (LearningGoal $goal): bool => $goal->id() !== $id,
        )));
    }
}
