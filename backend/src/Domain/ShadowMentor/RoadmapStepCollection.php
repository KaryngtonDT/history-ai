<?php

declare(strict_types=1);

namespace App\Domain\ShadowMentor;

final readonly class RoadmapStepCollection
{
    /** @param list<RoadmapStep> $items */
    public function __construct(private array $items)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /** @return list<RoadmapStep> */
    public function all(): array
    {
        return $this->items;
    }

    public function append(RoadmapStep $step): self
    {
        return new self([...$this->items, $step]);
    }
}
