<?php

declare(strict_types=1);

namespace App\Domain\ShadowMentor;

use App\Domain\ShadowGoals\GoalMilestone;

final readonly class GoalMilestoneCollection
{
    /** @param list<GoalMilestone> $items */
    public function __construct(private array $items)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /** @return list<GoalMilestone> */
    public function all(): array
    {
        return $this->items;
    }

    public function next(): ?GoalMilestone
    {
        foreach ($this->items as $item) {
            if (!$item->completed()) {
                return $item;
            }
        }

        return null;
    }

    public function append(GoalMilestone $milestone): self
    {
        return new self([...$this->items, $milestone]);
    }
}
